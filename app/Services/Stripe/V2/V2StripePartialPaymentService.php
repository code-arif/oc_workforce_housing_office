<?php

namespace App\Services\Stripe\V2;

use App\Mail\Tenant\Payment\PaymentSuccessAdminMail;
use App\Mail\Tenant\Payment\PaymentSuccessTenantMail;
use App\Models\Invoice;
use App\Models\Lease\LeaseDocument;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Setting;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class V2StripePartialPaymentService
{
    // Platform fee percentage (0 = no fee, change if you want to charge platform fee)
    private const PLATFORM_FEE_PERCENTAGE = 0;

    public function __construct()
    {
        $setting = Setting::first();
        $secret = $setting?->stripe_secret ?? config('services.stripe.secret');
        Stripe::setApiKey($secret);
    }

    /**
     * Get the connected Stripe account for an invoice's property
     */
    private function getConnectedAccountId(Invoice $invoice): ?string
    {
        $propertyId = $invoice->lease?->property_id;
        if (!$propertyId) {
            return null;
        }

        $property = Property::find($propertyId);
        if (!$property) {
            return null;
        }

        // Only return if fully connected and active
        if ($property->hasStripeConnected()) {
            return $property->stripe_account_id;
        }

        Log::warning('[StripePartialPayment] Property does not have active Stripe account', [
            'property_id' => $property->id,
            'stripe_status' => $property->stripe_account_status,
        ]);

        return null;
    }

    /**
     * Get payment details for an invoice
     */
    public function getPaymentDetails($invoiceId, $tenantId)
    {
        $invoice = Invoice::with([
            'lease' => function ($q) {
                $q->with(['property', 'season', 'assignments.bed']);
            }
        ])
            ->where('id', $invoiceId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found or unauthorized'];
        }

        $lease = $invoice->lease;

        // Check if lease is signed
        $leaseDocument = LeaseDocument::where('lease_id', $lease->id)
            ->where('tenant_id', $tenantId)
            ->first();

        $leaseSigned = $leaseDocument && $leaseDocument->tenant_signed_at;

        if (!$leaseSigned) {
            return ['success' => false, 'message' => 'Lease must be signed before making payments'];
        }

        $canMakePayment = $this->checkPaymentEligibility($invoice, $tenantId);
        if (!$canMakePayment['eligible']) {
            return ['success' => false, 'message' => $canMakePayment['reason']];
        }

        $assignment = $lease->assignments->where('is_current', true)->first();

        // Check stripe connect status
        $property = $lease->property;
        $stripeConnected = $property?->hasStripeConnected() ?? false;

        return [
            'success' => true,
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'amount' => $invoice->amount,
                    'total_amount' => $invoice->total_amount,
                    'balance_due' => $invoice->balance_due,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                ],
                'lease' => [
                    'id' => $lease->id,
                    'rent_amount' => $lease->rent_amount,
                    'deposit_amount' => $lease->deposit_amount,
                    'deposit_collected' => $lease->deposit_collected,
                    'property' => $lease->property->name ?? 'N/A',
                    'unit' => $assignment ? $assignment->bed->bed_label : 'N/A',
                ],
                'payment_info' => [
                    'stripe_connected' => $stripeConnected,
                    'property_id' => $property?->id,
                ],
            ]
        ];
    }

    /**
     * Check payment eligibility
     */
    private function checkPaymentEligibility($invoice, $tenantId): array
    {
        if ($invoice->status === 'PAID') {
            return ['eligible' => false, 'reason' => 'Invoice is already paid'];
        }

        $leaseDocument = LeaseDocument::where('lease_id', $invoice->lease_id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$leaseDocument || !$leaseDocument->tenant_signed_at) {
            return ['eligible' => false, 'reason' => 'Lease must be signed before making payments'];
        }

        return ['eligible' => true];
    }

    /**
     * Create Stripe checkout session (with Connect and custom partial payment support)
     */
    public function createCheckoutSession($invoiceId, $tenantId, ?float $amountToPay = null, string $paymentMethodType = 'card'): array
    {
        DB::beginTransaction();

        try {
            $allowedPaymentMethods = ['card', 'us_bank_account'];
            if (!in_array($paymentMethodType, $allowedPaymentMethods, true)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Invalid payment method type. Allowed values: card, us_bank_account.',
                ];
            }

            $invoice = Invoice::with([
                'lease' => function ($q) {
                    $q->with(['property', 'season', 'assignments.bed.room']);
                },
                'tenant' => function ($q) {
                    $q->with(['profile', 'address']);
                }
            ])
                ->where('id', $invoiceId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice not found'];
            }

            $eligibility = $this->checkPaymentEligibility($invoice, $tenantId);
            if (!$eligibility['eligible']) {
                DB::rollBack();
                return ['success' => false, 'message' => $eligibility['reason']];
            }

            $lease = $invoice->lease;
            $tenant = $invoice->tenant;
            $assignment = $lease->assignments->where('is_current', true)->first();

            $balanceDue = round(floatval($invoice->balance_due), 2);
            if ($balanceDue <= 0) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice has no balance due'];
            }

            // Handle custom partial payment amount
            if ($amountToPay !== null) {
                $amountToPay = round(floatval($amountToPay), 2);
                if ($amountToPay <= 0) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
                }
                if ($amountToPay > $balanceDue) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => sprintf(
                            'Payment amount ($%.2f) cannot exceed balance due ($%.2f).',
                            $amountToPay,
                            $balanceDue
                        ),
                    ];
                }
                $baseAmount = $amountToPay;
            } else {
                $baseAmount = $balanceDue;
            }

            $setting = Setting::first();
            $processingFee = 0.00;

            if ($paymentMethodType === 'us_bank_account') {
                $achFlatFee    = round(floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00)), 2);
                $processingFee = $achFlatFee;
            } elseif ($paymentMethodType === 'card') {
                $cardPct       = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
                $cardFixed     = floatval($setting?->stripe_card_fee_fixed      ?? env('STRIPE_CARD_FEE_FIXED', 0.30));
                $processingFee = round(($baseAmount * ($cardPct / 100)) + $cardFixed, 2);
            }

            $totalCharge = round($baseAmount + $processingFee, 2);
            $amountInCents = (int) round($totalCharge * 100);

            // Sanity guard — Stripe minimum is $0.50
            if ($amountInCents < 50) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Total charge is below the Stripe minimum of $0.50.'];
            }

            // Get connected account for this property
            $connectedAccountId = $this->getConnectedAccountId($invoice);

            if (!$connectedAccountId) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'This property does not have a connected Stripe account. Please contact the admin.'
                ];
            }

            // Calculate platform fee (if any)
            $platformFeeInCents = (int) round($amountInCents * (self::PLATFORM_FEE_PERCENTAGE / 100));

            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $amountInCents,
                        'product_data' => [
                            'name' => $invoice->type === 'DEPOSIT' ? 'Security Deposit (Partial)' : 'Rent Payment (Partial)',
                            'description' => sprintf(
                                'Invoice %s - %s (%s) - Partial Payment',
                                $invoice->invoice_number,
                                $lease->property->name ?? 'Property',
                                $assignment ? $assignment->bed->bed_label : 'Unit'
                            ),
                        ],
                    ],
                    'quantity' => 1,
                ]
            ];

            $tenantProfile = $tenant->profile;
            $tenantAddress = $tenant->address;

            $metadata = [
                'invoice_id' => (string) $invoice->id,
                'tenant_id' => (string) $tenantId,
                'lease_id' => (string) $lease->id,
                'property_id' => (string) $lease->property_id,
                'connected_account_id' => $connectedAccountId,
                'base_amount' => (string) $baseAmount,
                'payment_amount' => (string) $baseAmount, // fallback compatibility
                'processing_fee' => (string) $processingFee,
                'total_charge' => (string) $totalCharge,
                'invoice_number' => $invoice->invoice_number,
                'invoice_type' => $invoice->type,
                'partial_payment' => 'true',

                'tenant_email' => $tenant->email,
                'tenant_name' => $tenantProfile
                    ? trim(($tenantProfile->first_name ?? '') . ' ' . ($tenantProfile->middle_name ?? '') . ' ' . ($tenantProfile->last_name ?? ''))
                    : 'N/A',
                'tenant_phone' => $tenantProfile->phone ?? 'N/A',

                'tenant_address' => $tenantAddress->address ?? 'N/A',
                'tenant_city' => $tenantAddress->city ?? 'N/A',
                'tenant_state' => $tenantAddress->state ?? 'N/A',
                'tenant_zip' => $tenantAddress->zip ?? 'N/A',
                'tenant_country' => $tenantAddress->country ?? 'USA',

                'property_name' => $lease->property->name ?? 'N/A',
                'property_address' => $lease->property->address ?? 'N/A',
                'unit' => $assignment ? $assignment->bed->bed_label : 'N/A',
                'lease_start_date' => $lease->start_date->format('Y-m-d'),
                'lease_end_date' => $lease->end_date->format('Y-m-d'),
                'rent_amount' => (string) $lease->rent_amount,
                'deposit_amount' => (string) $lease->deposit_amount,
                'payment_frequency' => $lease->payment_frequency,
                'payment_method_type' => $paymentMethodType,
            ];

            $successUrl = config('services.stripe.success_url', env('STRIPE_SUCCESS_URL'));
            $cancelUrl = config('services.stripe.cancel_url', env('STRIPE_CANCEL_URL'));

            $paymentMethodTypes = ['card'];
            if ($paymentMethodType === 'us_bank_account') {
                $paymentMethodTypes = ['us_bank_account'];
            }

            // Build session params
            $sessionParams = [
                'payment_method_types' => $paymentMethodTypes,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'customer_email' => $tenant->email,
                'client_reference_id' => (string) $invoice->id,
                'metadata' => $metadata,
                // DESTINATION CHARGE: money goes to platform first, then to connected account
                'payment_intent_data' => [
                    'transfer_data' => [
                        'destination' => $connectedAccountId,
                    ],
                    'metadata' => $metadata,
                ],
            ];

            // Add platform fee if configured
            if ($platformFeeInCents > 0) {
                $sessionParams['payment_intent_data']['application_fee_amount'] = $platformFeeInCents;
            }

            $session = Session::create($sessionParams);

            DB::commit();

            Log::info('[StripePartialPayment] Stripe checkout session created with connected account', [
                'session_id' => $session->id,
                'connected_account' => $connectedAccountId,
                'invoice_id' => $invoice->id,
                'amount' => $totalCharge,
                'processing_fee' => $processingFee,
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'base_amount' => $baseAmount,
                    'processing_fee' => $processingFee,
                    'total_amount' => $totalCharge,
                ],
                'tenant' => [
                    'name' => $metadata['tenant_name'],
                    'email' => $tenant->email,
                    'phone' => $metadata['tenant_phone'],
                ],
                'lease' => [
                    'property' => $metadata['property_name'],
                    'unit' => $metadata['unit'],
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[StripePartialPayment] Stripe checkout session creation failed: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payment session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create Stripe Payment Intent for Payment Element (Card + ACH/us_bank_account) supporting partial payments
     */
    public function createPaymentIntent(
        int|string $invoiceId,
        int|string $tenantId,
        ?float     $amountToPay      = null,
        string     $paymentMethodType = 'card'
    ): array {
        $allowedMethods = ['card', 'us_bank_account'];

        if (!in_array($paymentMethodType, $allowedMethods, true)) {
            return [
                'success' => false,
                'message' => 'Invalid payment method type. Allowed: card, us_bank_account.',
            ];
        }

        DB::beginTransaction();

        try {
            // 1. Load Invoice with all needed relations
            $invoice = Invoice::with([
                'lease' => fn($q) => $q->with([
                    'property',
                    'season',
                    'assignments.bed.room',
                ]),
                'tenant' => fn($q) => $q->with(['profile', 'address']),
            ])
                ->where('id', $invoiceId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice not found or access denied.'];
            }

            // 2. Eligibility check
            $eligibility = $this->checkPaymentEligibility($invoice, $tenantId);
            if (!$eligibility['eligible']) {
                DB::rollBack();
                return ['success' => false, 'message' => $eligibility['reason']];
            }

            $lease  = $invoice->lease;
            $tenant = $invoice->tenant;

            if (!$lease) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Lease not found for this invoice.'];
            }

            // 3. Resolve base amount
            $balanceDue = round(floatval($invoice->balance_due), 2);

            if ($balanceDue <= 0) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice has no balance due.'];
            }

            if ($amountToPay !== null) {
                $amountToPay = round(floatval($amountToPay), 2);

                if ($amountToPay <= 0) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
                }

                if ($amountToPay > $balanceDue) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => sprintf(
                            'Payment amount ($%.2f) cannot exceed balance due ($%.2f).',
                            $amountToPay,
                            $balanceDue
                        ),
                    ];
                }

                $baseAmount = $amountToPay;
            } else {
                $baseAmount = $balanceDue;
            }

            // 4. Verify connected Stripe account for this property
            $connectedAccountId = $this->getConnectedAccountId($invoice);

            if (!$connectedAccountId) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'This property does not have an active Stripe account. Please contact the admin.',
                ];
            }

            // 5. Calculate processing fee
            $setting       = Setting::first();
            $processingFee = 0.00;

            if ($paymentMethodType === 'us_bank_account') {
                $achFlatFee    = round(floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00)), 2);
                $processingFee = $achFlatFee;
            } elseif ($paymentMethodType === 'card') {
                $cardPct       = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
                $cardFixed     = floatval($setting?->stripe_card_fee_fixed      ?? env('STRIPE_CARD_FEE_FIXED', 0.30));
                $processingFee = round(($baseAmount * ($cardPct / 100)) + $cardFixed, 2);
            }

            $totalCharge   = round($baseAmount + $processingFee, 2);
            $amountInCents = (int) round($totalCharge * 100);

            // Sanity guard — Stripe minimum is $0.50
            if ($amountInCents < 50) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Total charge is below the Stripe minimum of $0.50.'];
            }

            // 6. Resolve Stripe Customer
            $tenantProfile   = $tenant->profile;
            $tenantAddress   = $tenant->address;
            $stripeCustomerId = null;

            $tenantFullName = $tenantProfile
                ? trim(
                    ($tenantProfile->first_name  ?? '') . ' ' .
                        ($tenantProfile->middle_name ?? '') . ' ' .
                        ($tenantProfile->last_name   ?? '')
                )
                : 'Tenant';

            try {
                $existingCustomers = Customer::all([
                    'email' => $tenant->email,
                    'limit' => 1,
                ]);

                if (!empty($existingCustomers->data)) {
                    $stripeCustomerId = $existingCustomers->data[0]->id;

                    Log::info('[StripePartialPayment] Existing Stripe customer found', [
                        'customer_id' => $stripeCustomerId,
                        'tenant_id'   => $tenantId,
                    ]);
                } else {
                    $newCustomer = Customer::create([
                        'email' => $tenant->email,
                        'name'  => $tenantFullName,
                        'phone' => $tenantProfile->phone ?? null,
                        'address' => [
                            'line1'       => $tenantAddress->address ?? null,
                            'city'        => $tenantAddress->city    ?? null,
                            'state'       => $tenantAddress->state   ?? null,
                            'postal_code' => $tenantAddress->zip     ?? null,
                            'country'     => $tenantAddress->country ?? 'US',
                        ],
                        'metadata' => [
                            'tenant_id' => (string) $tenantId,
                        ],
                    ]);
                    $stripeCustomerId = $newCustomer->id;

                    Log::info('[StripePartialPayment] New Stripe customer created', [
                        'customer_id' => $stripeCustomerId,
                        'tenant_id'   => $tenantId,
                    ]);
                }
            } catch (Exception $e) {
                Log::warning('[StripePartialPayment] Stripe customer resolve failed: ' . $e->getMessage(), [
                    'tenant_id' => $tenantId,
                ]);

                if ($paymentMethodType === 'us_bank_account') {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Unable to create Stripe customer required for ACH payment. Please try again.',
                    ];
                }
            }

            // 7. Build metadata
            $assignment = $lease->assignments->where('is_current', true)->first();

            $metadata = [
                'invoice_id'           => (string) $invoice->id,
                'tenant_id'            => (string) $tenantId,
                'lease_id'             => (string) $lease->id,
                'property_id'          => (string) $lease->property_id,
                'connected_account_id' => $connectedAccountId,

                'base_amount'          => (string) $baseAmount,
                'payment_amount'       => (string) $baseAmount, // fallback
                'processing_fee'       => (string) $processingFee,
                'total_charge'         => (string) $totalCharge,
                'partial_payment'      => 'true',

                'payment_method_type'  => $paymentMethodType,
                'invoice_number'       => $invoice->invoice_number,
                'invoice_type'         => $invoice->type,

                'tenant_name'          => $tenantFullName,
                'tenant_email'         => $tenant->email,
                'property_name'        => $lease->property->name ?? 'N/A',
                'unit'                 => $assignment?->bed->bed_label ?? 'N/A',
            ];

            // 8. Build PaymentIntent params
            $paymentIntentParams = [
                'amount'      => $amountInCents,
                'currency'    => 'usd',
                'description' => sprintf(
                    '%s - Invoice %s (%s) - Partial Payment',
                    $invoice->type === 'DEPOSIT' ? 'Security Deposit' : 'Rent Payment',
                    $invoice->invoice_number,
                    $lease->property->name ?? 'Property'
                ),
                'metadata'        => $metadata,
                'transfer_data'   => [
                    'destination' => $connectedAccountId,
                ],
                ...(self::PLATFORM_FEE_PERCENTAGE > 0 ? [
                    'application_fee_amount' => (int) round($amountInCents * (self::PLATFORM_FEE_PERCENTAGE / 100)),
                ] : []),
            ];

            // 8a. Card-specific params
            if ($paymentMethodType === 'card') {
                $paymentIntentParams['payment_method_types'] = ['card'];
                $paymentIntentParams['on_behalf_of'] = $connectedAccountId;

                if ($stripeCustomerId) {
                    $paymentIntentParams['customer'] = $stripeCustomerId;
                }
            }

            // 8b. ACH (us_bank_account) specific params
            if ($paymentMethodType === 'us_bank_account') {
                $paymentIntentParams['payment_method_types'] = ['us_bank_account'];

                if (!$stripeCustomerId) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'A Stripe customer is required for ACH payments.',
                    ];
                }
                $paymentIntentParams['customer'] = $stripeCustomerId;

                $paymentIntentParams['payment_method_options'] = [
                    'us_bank_account' => [
                        'verification_method'   => 'automatic',
                        'financial_connections' => [
                            'permissions' => ['payment_method', 'balances'],
                        ],
                    ],
                ];
            }

            // 9. Create PaymentIntent
            $paymentIntent = PaymentIntent::create($paymentIntentParams);

            DB::commit();

            Log::info('[StripePartialPayment] PaymentIntent created successfully', [
                'payment_intent_id'    => $paymentIntent->id,
                'invoice_id'           => $invoice->id,
                'payment_method_type'  => $paymentMethodType,
                'connected_account_id' => $connectedAccountId,
                'base_amount'          => $baseAmount,
                'processing_fee'       => $processingFee,
                'total_charge'         => $totalCharge,
                'amount_in_cents'      => $amountInCents,
            ]);

            return [
                'success'             => true,
                'client_secret'       => $paymentIntent->client_secret,
                'payment_intent_id'   => $paymentIntent->id,
                'payment_method_type' => $paymentMethodType,

                'base_amount'         => $baseAmount,
                'processing_fee'      => $processingFee,
                'total_charge'        => $totalCharge,

                'invoice' => [
                    'id'             => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type'           => $invoice->type,
                    'balance_due'    => $balanceDue,
                ],
                'tenant' => [
                    'name'  => $tenantFullName,
                    'email' => $tenant->email,
                ],
                'lease' => [
                    'property' => $lease->property->name ?? 'N/A',
                    'unit'     => $assignment?->bed->bed_label ?? 'N/A',
                ],

                '_frontend_hint' => [
                    'is_async' => $paymentMethodType === 'us_bank_account',
                    'expected_status' => $paymentMethodType === 'us_bank_account' ? 'processing' : 'succeeded',
                    'redirect' => $paymentMethodType === 'us_bank_account' ? 'if_required' : 'always',
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('[StripePartialPayment] PaymentIntent creation failed', [
                'invoice_id'           => $invoiceId,
                'tenant_id'            => $tenantId,
                'payment_method_type'  => $paymentMethodType,
                'error'                => $e->getMessage(),
                'trace'                => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payment intent: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment after Stripe redirect (delegated to unified StripePaymentService)
     */
    public function verifyPayment($sessionId): array
    {
        return app(V2StripePaymentService::class)->verifyPayment($sessionId);
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($tenantId): array
    {
        return Payment::with(['invoice', 'lease.property'])
            ->where('tenant_id', $tenantId)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'invoice_number' => $payment->invoice->invoice_number ?? 'N/A',
                    'property_name' => $payment->lease->property->name ?? 'N/A',
                    'reference_number' => $payment->reference_number,
                    'status' => $payment->review_status ?? 'confirmed',
                ];
            })
            ->toArray();
    }
}
