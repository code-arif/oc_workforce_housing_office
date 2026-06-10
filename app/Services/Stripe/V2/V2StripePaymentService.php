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

class V2StripePaymentService
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

        Log::warning('Property does not have active Stripe account', [
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

        // $firstUnpaidInvoice = Invoice::where('tenant_id', $tenantId)
        //     ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
        //     ->orderBy('due_date', 'asc')
        //     ->orderBy('created_at', 'asc')
        //     ->first();

        // if ($firstUnpaidInvoice && $firstUnpaidInvoice->id !== $invoice->id) {
        //     return [
        //         'eligible' => false,
        //         'reason' => 'Previous invoice must be paid first (Invoice #' . $firstUnpaidInvoice->invoice_number . ')'
        //     ];
        // }

        return ['eligible' => true];
    }

    /**
     * Create Stripe checkout session (with Connect support)
     */
    public function createCheckoutSession($invoiceId, $tenantId, string $paymentMethodType = 'card'): array
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

            $paymentAmount = floatval($invoice->balance_due);
            if ($paymentAmount <= 0) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice has no balance due'];
            }

            $setting       = Setting::first();
            $processingFee = 0.00;

            if ($paymentMethodType === 'us_bank_account') {
                $achFlatFee    = round(floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00)), 2);
                $processingFee = $achFlatFee;
            } elseif ($paymentMethodType === 'card') {
                $cardPct       = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
                $cardFixed     = floatval($setting?->stripe_card_fee_fixed      ?? env('STRIPE_CARD_FEE_FIXED', 0.30));
                $processingFee = round(($paymentAmount * ($cardPct / 100)) + $cardFixed, 2);
            }

            $totalCharge = round($paymentAmount + $processingFee, 2);
            $amountInCents = (int) round($totalCharge * 100);

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
                            'name' => $invoice->type === 'DEPOSIT' ? 'Security Deposit' : 'Rent Payment',
                            'description' => sprintf(
                                'Invoice %s - %s (%s)',
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
                'payment_amount' => (string) $paymentAmount,
                'processing_fee' => (string) $processingFee,
                'total_charge' => (string) $totalCharge,
                'invoice_number' => $invoice->invoice_number,
                'invoice_type' => $invoice->type,

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

            Log::info('Stripe checkout session created with connected account', [
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
                    'base_amount' => $paymentAmount,
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
            Log::error('Stripe checkout session creation failed: ' . $e->getMessage(), [
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
     * Create Stripe Payment Intent for Element
     */
    // public function createPaymentIntent($invoiceId, $tenantId, $amountToPay = null, $paymentMethodType = 'card'): array
    // {
    //     DB::beginTransaction();

    //     try {
    //         $invoice = Invoice::with([
    //             'lease' => function ($q) {
    //                 $q->with(['property', 'season', 'assignments.bed.room']);
    //             },
    //             'tenant' => function ($q) {
    //                 $q->with(['profile', 'address']);
    //             }
    //         ])
    //             ->where('id', $invoiceId)
    //             ->where('tenant_id', $tenantId)
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$invoice) {
    //             DB::rollBack();
    //             return ['success' => false, 'message' => 'Invoice not found'];
    //         }

    //         $eligibility = $this->checkPaymentEligibility($invoice, $tenantId);
    //         if (!$eligibility['eligible']) {
    //             DB::rollBack();
    //             return ['success' => false, 'message' => $eligibility['reason']];
    //         }

    //         $lease = $invoice->lease;
    //         $tenant = $invoice->tenant;
    //         $assignment = $lease->assignments->where('is_current', true)->first();

    //         // Handle partial payment amount
    //         $baseAmount = floatval($invoice->balance_due);
    //         if ($amountToPay !== null) {
    //             $requestedAmount = floatval($amountToPay);
    //             if ($requestedAmount <= 0) {
    //                 DB::rollBack();
    //                 return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
    //             }
    //             if ($requestedAmount > $baseAmount) {
    //                 DB::rollBack();
    //                 return ['success' => false, 'message' => 'Payment amount cannot exceed balance due.'];
    //             }
    //             $baseAmount = $requestedAmount;
    //         }

    //         if ($baseAmount <= 0) {
    //             DB::rollBack();
    //             return ['success' => false, 'message' => 'Invoice has no balance due'];
    //         }

    //         // Get connected account for this property
    //         $connectedAccountId = $this->getConnectedAccountId($invoice);

    //         if (!$connectedAccountId) {
    //             DB::rollBack();
    //             return [
    //                 'success' => false,
    //                 'message' => 'This property does not have a connected Stripe account. Please contact the admin.'
    //             ];
    //         }

    //         // Calculate processing fee
    //         $processingFee = 0.00;

    //         $setting = Setting::first();
    //         $achFee = floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00));
    //         $cardFeePercentage = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
    //         $cardFeeFixed = floatval($setting?->stripe_card_fee_fixed ?? env('STRIPE_CARD_FEE_FIXED', 0.30));

    //         if ($paymentMethodType === 'us_bank_account') {
    //             $processingFee = $achFee; // Flat fee for ACH
    //         } elseif ($paymentMethodType === 'card') {
    //             $processingFee = ($baseAmount * ($cardFeePercentage / 100)) + $cardFeeFixed; // Percentage + Fixed for Card
    //         }

    //         $totalCharge = round($baseAmount + $processingFee, 2);
    //         $amountInCents = (int) round($totalCharge * 100);

    //         $tenantProfile = $tenant->profile;
    //         $tenantAddress = $tenant->address;

    //         // Resolve or create Stripe Customer on Platform for ACH direct debit requirements
    //         $stripeCustomerId = null;
    //         try {
    //             $customers = Customer::all(['email' => $tenant->email, 'limit' => 1]);
    //             if (count($customers->data) > 0) {
    //                 $stripeCustomerId = $customers->data[0]->id;
    //             } else {
    //                 $newCustomer = Customer::create([
    //                     'email' => $tenant->email,
    //                     'name' => $tenantProfile ? trim(($tenantProfile->first_name ?? '') . ' ' . ($tenantProfile->last_name ?? '')) : 'Tenant',
    //                 ]);
    //                 $stripeCustomerId = $newCustomer->id;
    //             }
    //         } catch (Exception $e) {
    //             Log::warning('Stripe customer creation/retrieval failed: ' . $e->getMessage());
    //         }

    //         $metadata = [
    //             'invoice_id' => (string) $invoice->id,
    //             'tenant_id' => (string) $tenantId,
    //             'lease_id' => (string) $lease->id,
    //             'property_id' => (string) $lease->property_id,
    //             'connected_account_id' => $connectedAccountId,

    //             'base_amount' => (string) $baseAmount,
    //             'processing_fee' => (string) $processingFee,
    //             'total_charge' => (string) $totalCharge,

    //             'payment_method_type' => $paymentMethodType,
    //             'invoice_number' => $invoice->invoice_number,
    //             'invoice_type' => $invoice->type,
    //             'property_name' => $lease->property->name ?? 'N/A',
    //             'tenant_name' => $tenantProfile ? trim(($tenantProfile->first_name ?? '') . ' ' . ($tenantProfile->last_name ?? '')) : 'N/A',
    //         ];

    //         // Create PaymentIntent
    //         $paymentIntentParams = [
    //             'amount' => $amountInCents,
    //             'currency' => 'usd',
    //             'payment_method_types' => [$paymentMethodType],
    //             'description' => sprintf(
    //                 'Invoice %s - %s',
    //                 $invoice->invoice_number,
    //                 $lease->property->name ?? 'Property'
    //             ),
    //             'metadata' => $metadata,
    //             'transfer_data' => [
    //                 'destination' => $connectedAccountId,
    //             ],
    //         ];

    //         // ACH (us_bank_account) requires a Customer and verification_method
    //         if ($paymentMethodType === 'us_bank_account') {
    //             $paymentIntentParams['payment_method_options'] = [
    //                 'us_bank_account' => [
    //                     'verification_method' => 'automatic',
    //                     'financial_connections' => [
    //                         'permissions' => ['payment_method'],
    //                     ],
    //                 ],
    //             ];

    //             // Customer is REQUIRED for ACH direct debit
    //             if ($stripeCustomerId) {
    //                 $paymentIntentParams['customer'] = $stripeCustomerId;
    //             }
    //         } else {
    //             // on_behalf_of is safe for card but can fail for ACH
    //             // if connected account lacks ACH capabilities
    //             $paymentIntentParams['on_behalf_of'] = $connectedAccountId;

    //             if ($stripeCustomerId) {
    //                 $paymentIntentParams['customer'] = $stripeCustomerId;
    //             }
    //         }

    //         $paymentIntent = PaymentIntent::create($paymentIntentParams);

    //         DB::commit();

    //         return [
    //             'success' => true,
    //             'client_secret' => $paymentIntent->client_secret,
    //             'base_amount' => $baseAmount,
    //             'processing_fee' => $processingFee,
    //             'total_charge' => $totalCharge,
    //             'invoice' => [
    //                 'id' => $invoice->id,
    //                 'invoice_number' => $invoice->invoice_number,
    //                 'type' => $invoice->type,
    //                 'balance_due' => floatval($invoice->balance_due),
    //             ],
    //             'tenant' => [
    //                 'name' => $metadata['tenant_name'],
    //                 'email' => $tenant->email,
    //             ],
    //             'lease' => [
    //                 'property' => $metadata['property_name'],
    //             ],
    //         ];
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error('Stripe PaymentIntent creation failed: ' . $e->getMessage(), [
    //             'invoice_id' => $invoiceId,
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Failed to create payment intent: ' . $e->getMessage()
    //         ];
    //     }
    // }



    /**
     * Create Stripe Payment Intent for Payment Element (Card + ACH/us_bank_account)
     *
     * Flow:
     *  1. Validate invoice ownership & eligibility
     *  2. Resolve or create Stripe Customer (required for ACH mandate)
     *  3. Calculate processing fee based on payment method
     *  4. Build PaymentIntent params per method type
     *  5. Create PaymentIntent and return client_secret to frontend
     *
     * Notes:
     *  - Card:          synchronous → webhook fires `payment_intent.succeeded` immediately
     *  - ACH (us_bank): asynchronous → status = `processing` first, webhook fires 1–3 days later
     *  - Both methods use Stripe Connect destination charges (transfer_data.destination)
     */
    public function createPaymentIntent(
        int|string $invoiceId,
        int|string $tenantId,
        ?float     $amountToPay      = null,
        string     $paymentMethodType = 'card'
    ): array {
        // 0. Sanitise & validate payment method type
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
                ->lockForUpdate()           // Pessimistic lock — prevent race conditions
                ->first();

            if (!$invoice) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice not found or access denied.'];
            }

            // 2. Eligibility check (signed lease, unpaid status, etc.)
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
                // ACH: flat fee (e.g. $5.00)
                $achFlatFee    = round(floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00)), 2);
                $processingFee = $achFlatFee;
            } elseif ($paymentMethodType === 'card') {
                // Card: percentage + fixed (e.g. 2.9% + $0.30)
                $cardPct       = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
                $cardFixed     = floatval($setting?->stripe_card_fee_fixed      ?? env('STRIPE_CARD_FEE_FIXED', 0.30));
                $processingFee = round(($baseAmount * ($cardPct / 100)) + $cardFixed, 2);
            }

            $totalCharge   = round($baseAmount + $processingFee, 2);
            $amountInCents = (int) round($totalCharge * 100); // Stripe requires integer cents

            // Sanity guard — Stripe minimum is $0.50
            if ($amountInCents < 50) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Total charge is below the Stripe minimum of $0.50.'];
            }

            // 6. Resolve Stripe Customer (required for ACH mandate, useful for card)
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
                // Search for existing customer by email to avoid duplicates
                $existingCustomers = Customer::all([
                    'email' => $tenant->email,
                    'limit' => 1,
                ]);

                if (!empty($existingCustomers->data)) {
                    $stripeCustomerId = $existingCustomers->data[0]->id;

                    Log::info('Existing Stripe customer found', [
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

                    Log::info('New Stripe customer created', [
                        'customer_id' => $stripeCustomerId,
                        'tenant_id'   => $tenantId,
                    ]);
                }
            } catch (Exception $e) {
                // Non-fatal for card. Fatal for ACH (customer is required).
                Log::warning('Stripe customer resolve failed: ' . $e->getMessage(), [
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

            // 7. Build metadata (stored on PaymentIntent for webhook processing)
            $assignment = $lease->assignments->where('is_current', true)->first();

            $metadata = [
                // Core identifiers
                'invoice_id'           => (string) $invoice->id,
                'tenant_id'            => (string) $tenantId,
                'lease_id'             => (string) $lease->id,
                'property_id'          => (string) $lease->property_id,
                'connected_account_id' => $connectedAccountId,

                // Amounts
                'base_amount'          => (string) $baseAmount,
                'processing_fee'       => (string) $processingFee,
                'total_charge'         => (string) $totalCharge,

                // Payment context
                'payment_method_type'  => $paymentMethodType,
                'invoice_number'       => $invoice->invoice_number,
                'invoice_type'         => $invoice->type,

                // Human-readable (for email/webhook logs)
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
                    '%s - Invoice %s (%s)',
                    $invoice->type === 'DEPOSIT' ? 'Security Deposit' : 'Rent Payment',
                    $invoice->invoice_number,
                    $lease->property->name ?? 'Property'
                ),
                'metadata'        => $metadata,
                // Destination charge → funds go to platform, then transferred to connected account
                'transfer_data'   => [
                    'destination' => $connectedAccountId,
                ],
                // Platform application fee (0 = no fee; configure PLATFORM_FEE_PERCENTAGE to enable)
                ...(self::PLATFORM_FEE_PERCENTAGE > 0 ? [
                    'application_fee_amount' => (int) round($amountInCents * (self::PLATFORM_FEE_PERCENTAGE / 100)),
                ] : []),
            ];

            // 8a. Card-specific params
            if ($paymentMethodType === 'card') {
                $paymentIntentParams['payment_method_types'] = ['card'];

                // on_behalf_of: makes the charge appear from the connected account's perspective
                $paymentIntentParams['on_behalf_of'] = $connectedAccountId;

                if ($stripeCustomerId) {
                    $paymentIntentParams['customer'] = $stripeCustomerId;
                }
            }

            // 8b. ACH (us_bank_account) specific params
            if ($paymentMethodType === 'us_bank_account') {
                $paymentIntentParams['payment_method_types'] = ['us_bank_account'];

                // Customer is REQUIRED for ACH — mandate must be attached to a customer
                if (!$stripeCustomerId) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'A Stripe customer is required for ACH payments.',
                    ];
                }
                $paymentIntentParams['customer'] = $stripeCustomerId;

                // ACH options: use Financial Connections for instant bank verification
                $paymentIntentParams['payment_method_options'] = [
                    'us_bank_account' => [
                        'verification_method'   => 'automatic', // instant via Financial Connections
                        'financial_connections' => [
                            'permissions' => ['payment_method', 'balances'],
                        ],
                    ],
                ];
            }

            // 9. Create PaymentIntent
            $paymentIntent = PaymentIntent::create($paymentIntentParams);

            DB::commit();

            Log::info('PaymentIntent created successfully', [
                'payment_intent_id'    => $paymentIntent->id,
                'invoice_id'           => $invoice->id,
                'payment_method_type'  => $paymentMethodType,
                'connected_account_id' => $connectedAccountId,
                'base_amount'          => $baseAmount,
                'processing_fee'       => $processingFee,
                'total_charge'         => $totalCharge,
                'amount_in_cents'      => $amountInCents,
            ]);

            // 10. Return response to frontend
            return [
                'success'             => true,
                'client_secret'       => $paymentIntent->client_secret,
                'payment_intent_id'   => $paymentIntent->id,
                'payment_method_type' => $paymentMethodType,

                // Amounts (for UI display)
                'base_amount'         => $baseAmount,
                'processing_fee'      => $processingFee,
                'total_charge'        => $totalCharge,

                // Context
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

                /*
             * Frontend behaviour guide (read this in your React component):
             *
             *  payment_method_type = 'card'
             *    → After confirmPayment() → status: 'succeeded' immediately
             *    → Webhook: payment_intent.succeeded fires right away
             *
             *  payment_method_type = 'us_bank_account'
             *    → After confirmPayment() → status: 'processing' (NORMAL — not an error)
             *    → Bank transfer settles in 1–3 business days
             *    → Webhook: payment_intent.succeeded fires when bank clears the transfer
             *    → Show user: "Your payment is processing, you'll get a confirmation email."
             */
                '_frontend_hint' => [
                    'is_async' => $paymentMethodType === 'us_bank_account',
                    'expected_status' => $paymentMethodType === 'us_bank_account' ? 'processing' : 'succeeded',
                    'redirect' => $paymentMethodType === 'us_bank_account' ? 'if_required' : 'always',
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('PaymentIntent creation failed', [
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
     * Verify payment after Stripe redirect
     */
    public function verifyPayment($sessionId): array
    {
        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return ['success' => false, 'message' => 'Payment not completed'];
            }

            $metadata = $session->metadata;
            return $this->processPayment($metadata, $session);
        } catch (Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process payment after successful Stripe payment
     */
    private function processPayment($metadata, $sessionOrIntent): array
    {
        DB::beginTransaction();

        try {
            $invoiceId = $metadata['invoice_id'] ?? null;
            $tenantId = $metadata['tenant_id'] ?? null;

            if (!$invoiceId) {
                DB::rollBack();
                Log::warning('Payment processing skipped: Missing invoice_id in metadata (Likely a Stripe test webhook)', [
                    'session_or_intent_id' => $sessionOrIntent->id ?? 'unknown'
                ]);
                return ['success' => false, 'message' => 'Missing invoice_id in metadata'];
            }

            $baseAmount = floatval($metadata['base_amount'] ?? $metadata['payment_amount'] ?? 0);
            $processingFee = floatval($metadata['processing_fee'] ?? 0);
            $totalCharge = floatval($metadata['total_charge'] ?? $metadata['payment_amount'] ?? 0);
            $connectedAccountId = $metadata['connected_account_id'] ?? null;

            // Pessimistic Locking to prevent race conditions on partial payments
            $invoice = Invoice::with(['lease', 'tenant.profile'])
                ->lockForUpdate()
                ->findOrFail($invoiceId);

            $lease = $invoice->lease;
            $tenant = $invoice->tenant;

            if ($invoice->tenant_id != $tenantId) {
                throw new Exception('Unauthorized payment attempt');
            }

            // Idempotency check: check both gateway_transaction_id (session id) and stripe_payment_intent_id
            $stripePaymentIntentId = $sessionOrIntent->payment_intent ?? $sessionOrIntent->id;

            $existingPayment = Payment::where(function ($query) use ($sessionOrIntent, $stripePaymentIntentId) {
                $query->where('gateway_transaction_id', $sessionOrIntent->id)
                    ->orWhere('stripe_payment_intent_id', $stripePaymentIntentId);
            })->first();

            if ($existingPayment) {
                DB::rollBack();
                Log::info('Payment already processed, skipping', [
                    'session_id' => $sessionOrIntent->id,
                    'payment_intent_id' => $stripePaymentIntentId
                ]);
                return [
                    'success' => true, // Return true so webhook doesn't retry
                    'payment' => ['id' => $existingPayment->id],
                    'invoice' => ['id' => $invoice->id],
                ];
            }

            $bedId = $lease->assignments()->where('is_current', true)->first()?->bed_id ?? null;

            $stripePaymentIntentId = $sessionOrIntent->payment_intent ?? $sessionOrIntent->id;

            $isPartial = isset($metadata['partial_payment']) && $metadata['partial_payment'] === 'true';

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenantId,
                'lease_id' => $lease->id,
                'bed_id' => $bedId,
                'payment_number' => 'PAY-' . strtoupper(uniqid()),
                'amount' => $baseAmount,
                'base_amount' => $baseAmount,
                'processing_fee' => $processingFee,
                'total_charged' => $totalCharge,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'stripe',
                'reference_number' => $stripePaymentIntentId,
                'gateway_transaction_id' => $sessionOrIntent->id,
                'stripe_payment_intent_id' => $stripePaymentIntentId,
                'payment_type' => $invoice->type === 'DEPOSIT' ? 'deposit' : 'rent',
                'paid_by' => 'tenant',
                'review_status' => 'confirmed', // Auto-confirm stripe payments
                'note' => sprintf(
                    '%s%s payment via Stripe - Invoice %s',
                    $invoice->type === 'DEPOSIT' ? 'Security deposit' : 'Rent',
                    $isPartial ? ' partial' : '',
                    $invoice->invoice_number
                ),
                'metadata' => [
                    'stripe_session_id' => $sessionOrIntent->id,
                    'stripe_payment_intent' => $stripePaymentIntentId,
                    'connected_account_id' => $connectedAccountId,
                    'tenant_name' => $metadata['tenant_name'] ?? 'N/A',
                    'property_name' => $metadata['property_name'] ?? 'N/A',
                    'property_id' => $metadata['property_id'] ?? null,
                    'partial_payment' => $isPartial ? 'true' : 'false',
                ]
            ]);

            Log::info('Payment record created in database', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $payment->amount,
                'processing_fee' => $payment->processing_fee,
                'total_charged' => $payment->total_charged,
            ]);

            // Update invoice - ONLY credit base_amount towards balance
            $newPaidAmount = floatval($invoice->paid_amount) + $baseAmount;
            $newBalance = floatval($invoice->total_amount) - $newPaidAmount;
            $status = 'PARTIAL';
            $paidAt = null;

            if ($newBalance <= 0.01) {
                $status = 'PAID';
                $paidAt = now();
                $newBalance = 0;
            }

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'balance_due' => max(0, $newBalance),
                'status' => $status,
                'paid_at' => $paidAt,
                'stripe_payment_method' => $metadata['payment_method_type'] ?? $invoice->stripe_payment_method,
                'stripe_exact_amount' => floatval($invoice->stripe_exact_amount) + $totalCharge,
            ]);

            if ($invoice->type === 'DEPOSIT' && $status === 'PAID') {
                $lease->update(['deposit_collected' => true]);
            }

            Transaction::create([
                'tenant_id' => $tenantId,
                'bed_id' => $bedId,
                'lease_id' => $lease->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'transaction_number' => 'TXN-' . strtoupper(uniqid()),
                'type' => 'payment',
                'entry_type' => 'credit',
                'amount' => $baseAmount,
                'transaction_date' => now()->toDateString(),
                'description' => sprintf(
                    '%s%s payment for Invoice %s - %s (via Stripe Connect → %s)',
                    $invoice->type === 'DEPOSIT' ? 'Deposit' : 'Rent',
                    $isPartial ? ' partial' : '',
                    $invoice->invoice_number,
                    $metadata['property_name'] ?? 'Property',
                    $connectedAccountId ?? 'platform'
                ),
                'metadata' => [
                    'payment_method' => 'stripe',
                    'stripe_session_id' => $sessionOrIntent->id,
                    'stripe_payment_intent' => $stripePaymentIntentId,
                    'connected_account_id' => $connectedAccountId,
                    'partial_payment' => $isPartial ? 'true' : 'false',
                ]
            ]);

            DB::commit();

            Log::info('Payment processed successfully via Stripe Connect', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'connected_account' => $connectedAccountId,
                'amount' => $baseAmount,
                'processing_fee' => $processingFee,
            ]);

            $this->sendPaymentEmails($payment, $invoice, $tenant, $metadata);

            return [
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => $baseAmount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => 'stripe',
                    'invoice_type' => $invoice->type,
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'paid_amount' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage(), [
                'session_id' => $sessionOrIntent->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Send payment success emails
     */
    private function sendPaymentEmails($payment, $invoice, $tenant, $metadata): void
    {
        try {
            $emailData = [
                'payment' => $payment,
                'invoice' => $invoice,
                'tenant' => $tenant,
                'tenant_name' => $metadata['tenant_name'] ?? 'Tenant',
                'property_name' => $metadata['property_name'] ?? 'Property',
                'unit' => $metadata['unit'] ?? 'N/A',
                'payment_amount' => $payment->amount,
                'invoice_number' => $invoice->invoice_number,
                'payment_date' => $payment->payment_date,
            ];

            Mail::to($tenant->email)->queue(new PaymentSuccessTenantMail($emailData));

            $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL'));
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new PaymentSuccessAdminMail($emailData));
            }
        } catch (Exception $e) {
            Log::error('Failed to send payment emails: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook($request): array
    {
        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (Exception $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Webhook signature verification failed'];
        }

        switch ($event->type) {
            // Checkout Session Events
            case 'checkout.session.completed':
                $session = $event->data->object;
                Log::info('Checkout session completed via webhook', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                ]);

                if ($session->payment_status === 'paid') {
                    // Route to multi-invoice handler if this is a bulk-payment session
                    if (($session->metadata['bulk_payment'] ?? '') === 'true') {
                        $multiService = app(V2StripeMultiPaymentService::class);
                        $result = $multiService->processMultiInvoicePayment($session->metadata, $session);
                    } else {
                        $result = $this->processPayment($session->metadata, $session);
                    }

                    if (!$result['success']) {
                        Log::error('Webhook payment processing failed', [
                            'session_id' => $session->id,
                            'error'      => $result['message']
                        ]);
                    }
                }
                break;

            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                Log::info('Checkout session async payment succeeded via webhook', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                ]);

                // Route to multi-invoice handler if this is a bulk-payment session
                if (($session->metadata['bulk_payment'] ?? '') === 'true') {
                    $multiService = app(V2StripeMultiPaymentService::class);
                    $result = $multiService->processMultiInvoicePayment($session->metadata, $session);
                } else {
                    $result = $this->processPayment($session->metadata, $session);
                }

                if (!$result['success']) {
                    Log::error('Webhook async payment processing failed', [
                        'session_id' => $session->id,
                        'error'      => $result['message']
                    ]);
                }
                break;

            case 'checkout.session.async_payment_failed':
                $session = $event->data->object;
                Log::warning('Checkout session async payment failed via webhook', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                    'failure_message' => $session->payment_intent->last_payment_error->message ?? 'N/A',
                ]);
                break;

            case 'transfer.created':
                // Log when transfer to connected account is created
                $transfer = $event->data->object;
                Log::info('Transfer created to connected account', [
                    'transfer_id' => $transfer->id,
                    'destination' => $transfer->destination,
                    'amount' => $transfer->amount / 100,
                ]);
                break;

            default:
                Log::info('Unhandled webhook event type: ' . $event->type);
        }

        return ['success' => true];
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
