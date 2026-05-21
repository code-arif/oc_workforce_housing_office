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
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class V2StripePaymentService
{
    // Platform fee percentage (0 = no fee, change if you want to charge platform fee)
    private const PLATFORM_FEE_PERCENTAGE = 0;

    public function __construct()
    {
        $setting = \App\Models\Setting::first();
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
    public function createCheckoutSession($invoiceId, $tenantId): array
    {
        DB::beginTransaction();

        try {
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

            $amountInCents = (int) round($paymentAmount * 100);

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
            ];

            $successUrl = config('services.stripe.success_url', env('STRIPE_SUCCESS_URL'));
            $cancelUrl = config('services.stripe.cancel_url', env('STRIPE_CANCEL_URL'));

            // Build session params
            $sessionParams = [
                'payment_method_types' => ['card'],
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
                'amount' => $paymentAmount,
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'amount' => $paymentAmount,
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
    public function createPaymentIntent($invoiceId, $tenantId, $amountToPay = null, $paymentMethodType = 'card'): array
    {
        DB::beginTransaction();

        try {
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

            // Handle partial payment amount
            $baseAmount = floatval($invoice->balance_due);
            if ($amountToPay !== null) {
                $requestedAmount = floatval($amountToPay);
                if ($requestedAmount <= 0) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
                }
                if ($requestedAmount > $baseAmount) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Payment amount cannot exceed balance due.'];
                }
                $baseAmount = $requestedAmount;
            }

            if ($baseAmount <= 0) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invoice has no balance due'];
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

            // Calculate processing fee
            $processingFee = 0.00;

            $setting = Setting::first();
            $achFee = floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00));
            $cardFeePercentage = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
            $cardFeeFixed = floatval($setting?->stripe_card_fee_fixed ?? env('STRIPE_CARD_FEE_FIXED', 0.30));

            if ($paymentMethodType === 'us_bank_account') {
                $processingFee = $achFee; // Flat fee for ACH
            } elseif ($paymentMethodType === 'card') {
                $processingFee = ($baseAmount * ($cardFeePercentage / 100)) + $cardFeeFixed; // Percentage + Fixed for Card
            }

            $totalCharge = round($baseAmount + $processingFee, 2);
            $amountInCents = (int) round($totalCharge * 100);

            $tenantProfile = $tenant->profile;
            $tenantAddress = $tenant->address;

            $metadata = [
                'invoice_id' => (string) $invoice->id,
                'tenant_id' => (string) $tenantId,
                'lease_id' => (string) $lease->id,
                'property_id' => (string) $lease->property_id,
                'connected_account_id' => $connectedAccountId,

                'base_amount' => (string) $baseAmount,
                'processing_fee' => (string) $processingFee,
                'total_charge' => (string) $totalCharge,

                'payment_method_type' => $paymentMethodType,
                'invoice_number' => $invoice->invoice_number,
                'invoice_type' => $invoice->type,
                'property_name' => $lease->property->name ?? 'N/A',
                'tenant_name' => $tenantProfile ? trim(($tenantProfile->first_name ?? '') . ' ' . ($tenantProfile->last_name ?? '')) : 'N/A',
            ];

            // Create PaymentIntent
            $paymentIntentParams = [
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method_types' => [$paymentMethodType],
                'description' => sprintf(
                    'Invoice %s - %s',
                    $invoice->invoice_number,
                    $lease->property->name ?? 'Property'
                ),
                'metadata' => $metadata,
                'transfer_data' => [
                    'destination' => $connectedAccountId,
                ],
            ];

            $paymentIntent = PaymentIntent::create($paymentIntentParams);

            DB::commit();

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'base_amount' => $baseAmount,
                'processing_fee' => $processingFee,
                'total_charge' => $totalCharge,
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'balance_due' => floatval($invoice->balance_due),
                ],
                'tenant' => [
                    'name' => $metadata['tenant_name'],
                    'email' => $tenant->email,
                ],
                'lease' => [
                    'property' => $metadata['property_name'],
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Stripe PaymentIntent creation failed: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payment intent: ' . $e->getMessage()
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
            $invoiceId = $metadata['invoice_id'];
            $tenantId = $metadata['tenant_id'];
            $baseAmount = floatval($metadata['base_amount'] ?? $metadata['payment_amount']);
            $processingFee = floatval($metadata['processing_fee'] ?? 0);
            $totalCharge = floatval($metadata['total_charge'] ?? $metadata['payment_amount']);
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

            // Idempotency check
            $existingPayment = Payment::where('gateway_transaction_id', $sessionOrIntent->id)->first();
            if ($existingPayment) {
                DB::rollBack();
                Log::info('Payment already processed, skipping', ['session_id' => $sessionOrIntent->id]);
                return [
                    'success' => true, // Return true so webhook doesn't retry
                    'payment' => ['id' => $existingPayment->id],
                    'invoice' => ['id' => $invoice->id],
                ];
            }

            $bedId = $lease->assignments()->where('is_current', true)->first()?->bed_id ?? null;

            $stripePaymentIntentId = $sessionOrIntent->payment_intent ?? $sessionOrIntent->id;

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
                    '%s payment via Stripe - Invoice %s',
                    $invoice->type === 'DEPOSIT' ? 'Security deposit' : 'Rent',
                    $invoice->invoice_number
                ),
                'metadata' => [
                    'stripe_session_id' => $sessionOrIntent->id,
                    'stripe_payment_intent' => $stripePaymentIntentId,
                    'connected_account_id' => $connectedAccountId,
                    'tenant_name' => $metadata['tenant_name'] ?? 'N/A',
                    'property_name' => $metadata['property_name'] ?? 'N/A',
                    'property_id' => $metadata['property_id'] ?? null,
                ]
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
                    '%s payment for Invoice %s - %s (via Stripe Connect → %s)',
                    $invoice->type === 'DEPOSIT' ? 'Deposit' : 'Rent',
                    $invoice->invoice_number,
                    $metadata['property_name'] ?? 'Property',
                    $connectedAccountId ?? 'platform'
                ),
                'metadata' => [
                    'payment_method' => 'stripe',
                    'stripe_session_id' => $sessionOrIntent->id,
                    'stripe_payment_intent' => $stripePaymentIntentId,
                    'connected_account_id' => $connectedAccountId,
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
            case 'payment_intent.succeeded':
                $intent = $event->data->object;
                Log::info('PaymentIntent succeeded via webhook', [
                    'intent_id' => $intent->id,
                ]);

                $result = $this->processPayment($intent->metadata, $intent);
                if (!$result['success']) {
                    Log::error('Webhook payment processing failed for intent', [
                        'intent_id' => $intent->id,
                        'error' => $result['message']
                    ]);
                }
                break;

            case 'checkout.session.completed':
                $session = $event->data->object;
                Log::info('Checkout session completed via webhook', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                ]);

                if ($session->payment_status === 'paid') {
                    $result = $this->processPayment($session->metadata, $session);
                    if (!$result['success']) {
                        Log::error('Webhook payment processing failed', [
                            'session_id' => $session->id,
                            'error' => $result['message']
                        ]);
                    }
                }
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
