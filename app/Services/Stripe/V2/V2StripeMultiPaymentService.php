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
use Stripe\Stripe;
use Stripe\Webhook;

class V2StripeMultiPaymentService
{
    // Platform fee percentage (0 = no fee)
    private const PLATFORM_FEE_PERCENTAGE = 0;

    public function __construct()
    {
        $setting = Setting::first();
        $secret  = $setting?->stripe_secret ?? config('services.stripe.secret');
        Stripe::setApiKey($secret);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Get details for multiple invoices before creating checkout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return details and fee summary for a set of invoices.
     *
     * @param  int[]  $invoiceIds
     * @param  int    $tenantId
     * @param  string $paymentMethodType  'card' | 'us_bank_account'
     */
    public function getMultiPaymentDetails(array $invoiceIds, int $tenantId, string $paymentMethodType = 'card'): array
    {
        try {
            // 1. Validate payment method
            if (!in_array($paymentMethodType, ['card', 'us_bank_account'], true)) {
                return ['success' => false, 'message' => 'Invalid payment method type.'];
            }

            // 2. Load all invoices
            $invoices = Invoice::with([
                'lease' => fn($q) => $q->with(['property', 'assignments.bed']),
            ])
                ->whereIn('id', $invoiceIds)
                ->where('tenant_id', $tenantId)
                ->get();

            if ($invoices->count() !== count($invoiceIds)) {
                return ['success' => false, 'message' => 'One or more invoices not found or not accessible.'];
            }

            // 3. Validate each invoice
            $errors = [];
            foreach ($invoices as $invoice) {
                $check = $this->checkInvoiceEligibility($invoice, $tenantId);
                if (!$check['eligible']) {
                    $errors[] = "Invoice #{$invoice->invoice_number}: {$check['reason']}";
                }
            }

            if (!empty($errors)) {
                return ['success' => false, 'message' => implode('; ', $errors)];
            }

            // 4. All invoices must share the same connected Stripe account (same property)
            $connectedAccountId = $this->resolveConnectedAccount($invoices);
            if (!$connectedAccountId) {
                return [
                    'success' => false,
                    'message' => 'All invoices must belong to a property with an active Stripe account.',
                ];
            }

            // 5. Calculate fees
            $totalBaseAmount = $invoices->sum(fn($i) => floatval($i->balance_due));
            $totalBaseAmount = round($totalBaseAmount, 2);

            $setting       = Setting::first();
            $processingFee = $this->calculateProcessingFee($totalBaseAmount, $paymentMethodType, $setting);
            $totalCharge   = round($totalBaseAmount + $processingFee, 2);

            // 6. Build invoice summary list
            $firstInvoice  = $invoices->first();
            $lease         = $firstInvoice->lease;
            $assignment    = $lease?->assignments->where('is_current', true)->first();
            $stripeConnected = !empty($connectedAccountId);

            return [
                'success' => true,
                'data'    => [
                    'invoices' => $invoices->map(fn($inv) => [
                        'id'             => $inv->id,
                        'invoice_number' => $inv->invoice_number,
                        'type'           => $inv->type,
                        'balance_due'    => floatval($inv->balance_due),
                        'due_date'       => $inv->due_date?->format('Y-m-d'),
                        'status'         => $inv->status,
                    ])->values(),

                    'summary' => [
                        'total_base_amount' => $totalBaseAmount,
                        'processing_fee'    => $processingFee,
                        'total_charge'      => $totalCharge,
                        'payment_method'    => $paymentMethodType,
                        'invoice_count'     => $invoices->count(),
                    ],

                    'lease' => [
                        'property' => $lease?->property?->name ?? 'N/A',
                        'unit'     => $assignment?->bed?->bed_label ?? 'N/A',
                    ],

                    'payment_info' => [
                        'stripe_connected' => $stripeConnected,
                    ],
                ],
            ];
        } catch (Exception $e) {
            Log::error('[MultiPayment] getMultiPaymentDetails failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to fetch payment details: ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Create Checkout Session for multiple invoices
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  int[]  $invoiceIds
     * @param  int    $tenantId
     * @param  string $paymentMethodType
     */
    public function createCheckoutSession(array $invoiceIds, int $tenantId, string $paymentMethodType = 'card'): array
    {
        DB::beginTransaction();

        try {
            // 1. Validate payment method
            if (!in_array($paymentMethodType, ['card', 'us_bank_account'], true)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Invalid payment method type.'];
            }

            // 2. Load invoices with lock
            $invoices = Invoice::with([
                'lease' => fn($q) => $q->with(['property', 'season', 'assignments.bed.room']),
                'tenant' => fn($q) => $q->with(['profile', 'address']),
            ])
                ->whereIn('id', $invoiceIds)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->get();

            if ($invoices->count() !== count($invoiceIds)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'One or more invoices not found or not accessible.'];
            }

            // 3. Validate all invoices
            $errors = [];
            foreach ($invoices as $invoice) {
                $check = $this->checkInvoiceEligibility($invoice, $tenantId);
                if (!$check['eligible']) {
                    $errors[] = "Invoice #{$invoice->invoice_number}: {$check['reason']}";
                }
            }
            if (!empty($errors)) {
                DB::rollBack();
                return ['success' => false, 'message' => implode('; ', $errors)];
            }

            // 4. Resolve connected Stripe account
            $connectedAccountId = $this->resolveConnectedAccount($invoices);
            if (!$connectedAccountId) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'All invoices must belong to a property with an active Stripe account.',
                ];
            }

            // 5. Tenant / lease context (from first invoice)
            $firstInvoice  = $invoices->first();
            $lease         = $firstInvoice->lease;
            $tenant        = $firstInvoice->tenant;
            $tenantProfile = $tenant->profile;
            $tenantAddress = $tenant->address;
            $assignment    = $lease->assignments->where('is_current', true)->first();

            $tenantFullName = $tenantProfile
                ? trim(($tenantProfile->first_name ?? '') . ' ' . ($tenantProfile->middle_name ?? '') . ' ' . ($tenantProfile->last_name ?? ''))
                : 'Tenant';

            // 6. Calculate amounts
            $setting         = Setting::first();
            $totalBaseAmount = round($invoices->sum(fn($i) => floatval($i->balance_due)), 2);

            if ($totalBaseAmount <= 0) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Total balance due is zero; nothing to pay.'];
            }

            $processingFee = $this->calculateProcessingFee($totalBaseAmount, $paymentMethodType, $setting);
            $totalCharge   = round($totalBaseAmount + $processingFee, 2);
            $amountInCents = (int) round($totalCharge * 100);

            if ($amountInCents < 50) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Total charge is below the Stripe minimum of $0.50.'];
            }

            // 7. Build line_items — one per invoice
            $lineItems = $invoices->map(function ($invoice) {
                $invoiceAmountCents = (int) round(floatval($invoice->balance_due) * 100);
                return [
                    'price_data' => [
                        'currency'     => 'usd',
                        'unit_amount'  => $invoiceAmountCents,
                        'product_data' => [
                            'name'        => ($invoice->type === 'DEPOSIT' ? 'Security Deposit' : 'Rent Payment') . ' — Invoice ' . $invoice->invoice_number,
                            'description' => sprintf(
                                'Due: %s | Balance: $%.2f',
                                $invoice->due_date?->format('M d, Y') ?? 'N/A',
                                floatval($invoice->balance_due)
                            ),
                        ],
                    ],
                    'quantity' => 1,
                ];
            })->toArray();

            // Add processing fee as a separate line item if > 0
            if ($processingFee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'usd',
                        'unit_amount'  => (int) round($processingFee * 100),
                        'product_data' => [
                            'name'        => 'Processing Fee',
                            'description' => $paymentMethodType === 'us_bank_account'
                                ? 'ACH Bank Transfer Processing Fee'
                                : 'Card Processing Fee (2.9% + $0.30)',
                        ],
                    ],
                    'quantity' => 1,
                ];
            }

            // 8. Build metadata
            $invoiceIdsStr     = implode(',', $invoices->pluck('id')->toArray());
            $invoiceNumbersStr = implode(',', $invoices->pluck('invoice_number')->toArray());
            $invoiceAmountsStr = implode(',', $invoices->map(fn($i) => number_format(floatval($i->balance_due), 2, '.', ''))->toArray());

            $metadata = [
                // Multi-payment markers
                'bulk_payment'         => 'true',
                'invoice_ids'          => $invoiceIdsStr,
                'invoice_count'        => (string) $invoices->count(),
                'invoice_numbers'      => $invoiceNumbersStr,
                'invoice_amounts'      => $invoiceAmountsStr,  // per-invoice base amounts

                // Amounts
                'total_base_amount'    => (string) $totalBaseAmount,
                'base_amount'          => (string) $totalBaseAmount,  // fallback for shared processPayment
                'processing_fee'       => (string) $processingFee,
                'total_charge'         => (string) $totalCharge,

                // Context
                'tenant_id'            => (string) $tenantId,
                'lease_id'             => (string) $lease->id,
                'property_id'          => (string) $lease->property_id,
                'connected_account_id' => $connectedAccountId,
                'payment_method_type'  => $paymentMethodType,

                // Human-readable
                'tenant_name'          => $tenantFullName,
                'tenant_email'         => $tenant->email,
                'property_name'        => $lease->property->name ?? 'N/A',
                'unit'                 => $assignment?->bed?->bed_label ?? 'N/A',
            ];

            // 9. Build session params
            $successUrl = config('services.stripe.success_url', env('STRIPE_SUCCESS_URL'));
            $cancelUrl  = config('services.stripe.cancel_url', env('STRIPE_CANCEL_URL'));

            $paymentMethodTypes = $paymentMethodType === 'us_bank_account' ? ['us_bank_account'] : ['card'];

            $sessionParams = [
                'payment_method_types' => $paymentMethodTypes,
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'success_url'          => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => $cancelUrl,
                'customer_email'       => $tenant->email,
                'client_reference_id'  => 'multi_' . implode('_', $invoices->pluck('id')->toArray()),
                'metadata'             => $metadata,
                'payment_intent_data'  => [
                    'transfer_data' => [
                        'destination' => $connectedAccountId,
                    ],
                    'metadata'      => $metadata,
                ],
            ];

            // Platform fee if configured
            $platformFeeInCents = (int) round($amountInCents * (self::PLATFORM_FEE_PERCENTAGE / 100));
            if ($platformFeeInCents > 0) {
                $sessionParams['payment_intent_data']['application_fee_amount'] = $platformFeeInCents;
            }

            $session = Session::create($sessionParams);

            DB::commit();

            Log::info('[MultiPayment] Stripe multi-invoice checkout session created', [
                'session_id'          => $session->id,
                'invoice_ids'         => $invoiceIdsStr,
                'connected_account'   => $connectedAccountId,
                'total_charge'        => $totalCharge,
                'processing_fee'      => $processingFee,
            ]);

            return [
                'success'      => true,
                'session_id'   => $session->id,
                'checkout_url' => $session->url,
                'summary'      => [
                    'invoice_count'     => $invoices->count(),
                    'invoice_numbers'   => $invoices->pluck('invoice_number'),
                    'total_base_amount' => $totalBaseAmount,
                    'processing_fee'    => $processingFee,
                    'total_charge'      => $totalCharge,
                ],
                'tenant' => [
                    'name'  => $tenantFullName,
                    'email' => $tenant->email,
                ],
                'lease' => [
                    'property' => $lease->property->name ?? 'N/A',
                    'unit'     => $assignment?->bed?->bed_label ?? 'N/A',
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[MultiPayment] Checkout session creation failed: ' . $e->getMessage(), [
                'invoice_ids' => $invoiceIds,
                'trace'       => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Failed to create payment session: ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Verify payment after Stripe redirect (for dev/fallback)
    // ─────────────────────────────────────────────────────────────────────────

    public function verifyPayment(string $sessionId): array
    {
        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return ['success' => false, 'message' => 'Payment not completed.'];
            }

            return $this->processMultiInvoicePayment($session->metadata, $session);
        } catch (Exception $e) {
            Log::error('[MultiPayment] verifyPayment failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Process multi-invoice payment (called by webhook or verifyPayment)
    // ─────────────────────────────────────────────────────────────────────────

    public function processMultiInvoicePayment($metadata, $sessionOrIntent): array
    {
        DB::beginTransaction();

        try {
            // 1. Extract core metadata
            $invoiceIdsStr  = $metadata['invoice_ids'] ?? null;
            $tenantId       = $metadata['tenant_id']   ?? null;

            if (!$invoiceIdsStr || !$tenantId) {
                DB::rollBack();
                Log::warning('[MultiPayment] Missing invoice_ids or tenant_id in metadata', [
                    'session_id' => $sessionOrIntent->id ?? 'unknown',
                ]);
                return ['success' => false, 'message' => 'Missing invoice_ids or tenant_id in metadata.'];
            }

            $invoiceIds         = array_map('intval', explode(',', $invoiceIdsStr));
            $invoiceAmountsStr  = $metadata['invoice_amounts'] ?? '';
            $invoiceAmounts     = !empty($invoiceAmountsStr)
                ? array_map('floatval', explode(',', $invoiceAmountsStr))
                : [];

            $connectedAccountId = $metadata['connected_account_id'] ?? null;
            $processingFee      = floatval($metadata['processing_fee'] ?? 0);
            $totalCharge        = floatval($metadata['total_charge'] ?? $metadata['total_base_amount'] ?? 0);

            // 2. Global idempotency check — already processed this session for ANY of the invoices?
            $stripePaymentIntentId = $sessionOrIntent->payment_intent ?? $sessionOrIntent->id;

            $alreadyExists = Payment::where(function ($q) use ($sessionOrIntent, $stripePaymentIntentId) {
                $q->where('gateway_transaction_id', $sessionOrIntent->id)
                  ->orWhere('stripe_payment_intent_id', $stripePaymentIntentId);
            })->exists();

            if ($alreadyExists) {
                DB::rollBack();
                Log::info('[MultiPayment] Payment already processed (idempotency skip)', [
                    'session_id'   => $sessionOrIntent->id,
                    'invoice_ids'  => $invoiceIdsStr,
                ]);
                return [
                    'success'    => true,
                    'message'    => 'Payment already processed.',
                    'invoice_ids' => $invoiceIds,
                ];
            }

            // 3. Load all invoices with lock
            $invoices = Invoice::with(['lease', 'tenant.profile'])
                ->lockForUpdate()
                ->whereIn('id', $invoiceIds)
                ->where('tenant_id', $tenantId)
                ->get();

            if ($invoices->count() !== count($invoiceIds)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'One or more invoices not found during processing.'];
            }

            $firstInvoice = $invoices->first();
            $lease        = $firstInvoice->lease;
            $tenant       = $firstInvoice->tenant;
            $bedId        = $lease->assignments()->where('is_current', true)->first()?->bed_id ?? null;

            // 4. Process each invoice individually
            $processedPayments = [];
            $processedInvoices = [];

            foreach ($invoices as $index => $invoice) {
                // Per-invoice amount (from metadata); fallback to balance_due
                $invoiceBaseAmount = isset($invoiceAmounts[$index])
                    ? round($invoiceAmounts[$index], 2)
                    : round(floatval($invoice->balance_due), 2);

                // Attach processing fee only to the first invoice to avoid double-charging
                $invoiceProcessingFee = ($index === 0) ? $processingFee : 0.00;
                $invoiceTotalCharge   = round($invoiceBaseAmount + $invoiceProcessingFee, 2);

                // Note: invoice-level idempotency — check for this specific invoice
                $existingForInvoice = Payment::where('gateway_transaction_id', $sessionOrIntent->id)
                    ->where('invoice_id', $invoice->id)
                    ->exists();

                if ($existingForInvoice) {
                    Log::info('[MultiPayment] Invoice already has payment record, skipping', [
                        'invoice_id' => $invoice->id,
                    ]);
                    continue;
                }

                $payment = Payment::create([
                    'invoice_id'               => $invoice->id,
                    'tenant_id'                => $tenantId,
                    'lease_id'                 => $lease->id,
                    'bed_id'                   => $bedId,
                    'payment_number'           => 'PAY-' . strtoupper(uniqid()),
                    'amount'                   => $invoiceBaseAmount,
                    'base_amount'              => $invoiceBaseAmount,
                    'processing_fee'           => $invoiceProcessingFee,
                    'total_charged'            => $invoiceTotalCharge,
                    'payment_date'             => now()->toDateString(),
                    'payment_method'           => 'stripe',
                    'reference_number'         => $stripePaymentIntentId,
                    'gateway_transaction_id'   => $sessionOrIntent->id,
                    'stripe_payment_intent_id' => $stripePaymentIntentId,
                    'payment_type'             => $invoice->type === 'DEPOSIT' ? 'deposit' : 'rent',
                    'paid_by'                  => 'tenant',
                    'review_status'            => 'confirmed',
                    'note'                     => sprintf(
                        '%s payment via Stripe (bulk) - Invoice %s',
                        $invoice->type === 'DEPOSIT' ? 'Security deposit' : 'Rent',
                        $invoice->invoice_number
                    ),
                    'metadata' => [
                        'stripe_session_id'    => $sessionOrIntent->id,
                        'stripe_payment_intent'=> $stripePaymentIntentId,
                        'connected_account_id' => $connectedAccountId,
                        'bulk_payment'         => 'true',
                        'all_invoice_ids'      => $invoiceIdsStr,
                        'tenant_name'          => $metadata['tenant_name'] ?? 'N/A',
                        'property_name'        => $metadata['property_name'] ?? 'N/A',
                        'property_id'          => $metadata['property_id'] ?? null,
                    ],
                ]);

                // Update invoice balance
                $newPaidAmount = round(floatval($invoice->paid_amount) + $invoiceBaseAmount, 2);
                $newBalance    = round(floatval($invoice->total_amount) - $newPaidAmount, 2);
                $status        = 'PARTIAL';
                $paidAt        = null;

                if ($newBalance <= 0.01) {
                    $status   = 'PAID';
                    $paidAt   = now();
                    $newBalance = 0;
                }

                $invoice->update([
                    'paid_amount'          => $newPaidAmount,
                    'balance_due'          => max(0, $newBalance),
                    'status'               => $status,
                    'paid_at'              => $paidAt,
                    'stripe_payment_method'=> $metadata['payment_method_type'] ?? $invoice->stripe_payment_method,
                    'stripe_exact_amount'  => round(floatval($invoice->stripe_exact_amount) + $invoiceTotalCharge, 2),
                ]);

                // Mark deposit collected on lease if applicable
                if ($invoice->type === 'DEPOSIT' && $status === 'PAID') {
                    $lease->update(['deposit_collected' => true]);
                }

                // Create transaction record
                Transaction::create([
                    'tenant_id'          => $tenantId,
                    'bed_id'             => $bedId,
                    'lease_id'           => $lease->id,
                    'invoice_id'         => $invoice->id,
                    'payment_id'         => $payment->id,
                    'transaction_number' => 'TXN-' . strtoupper(uniqid()),
                    'type'               => 'payment',
                    'entry_type'         => 'credit',
                    'amount'             => $invoiceBaseAmount,
                    'transaction_date'   => now()->toDateString(),
                    'description'        => sprintf(
                        '%s payment (bulk) for Invoice %s - %s (via Stripe Connect → %s)',
                        $invoice->type === 'DEPOSIT' ? 'Deposit' : 'Rent',
                        $invoice->invoice_number,
                        $metadata['property_name'] ?? 'Property',
                        $connectedAccountId ?? 'platform'
                    ),
                    'metadata' => [
                        'payment_method'       => 'stripe',
                        'stripe_session_id'    => $sessionOrIntent->id,
                        'stripe_payment_intent'=> $stripePaymentIntentId,
                        'connected_account_id' => $connectedAccountId,
                        'bulk_payment'         => 'true',
                        'all_invoice_ids'      => $invoiceIdsStr,
                    ],
                ]);

                $processedPayments[] = [
                    'id'             => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount'         => $invoiceBaseAmount,
                ];

                $processedInvoices[] = [
                    'id'             => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status'         => $invoice->status,
                    'paid_amount'    => $invoice->paid_amount,
                    'balance_due'    => $invoice->balance_due,
                ];

                Log::info('[MultiPayment] Invoice payment processed', [
                    'payment_id'  => $payment->id,
                    'invoice_id'  => $invoice->id,
                    'amount'      => $invoiceBaseAmount,
                ]);
            }

            DB::commit();

            Log::info('[MultiPayment] All invoices processed successfully', [
                'session_id'         => $sessionOrIntent->id,
                'invoice_ids'        => $invoiceIdsStr,
                'total_charge'       => $totalCharge,
                'connected_account'  => $connectedAccountId,
            ]);

            // Send one combined email
            $this->sendBulkPaymentEmails($processedPayments, $processedInvoices, $tenant, $metadata, $totalCharge);

            return [
                'success'          => true,
                'payments'         => $processedPayments,
                'invoices'         => $processedInvoices,
                'total_charged'    => $totalCharge,
                'processing_fee'   => $processingFee,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[MultiPayment] processMultiInvoicePayment failed: ' . $e->getMessage(), [
                'session_id' => $sessionOrIntent->id ?? 'unknown',
                'trace'      => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Multi-invoice payment processing failed: ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Check a single invoice's eligibility for payment.
     */
    private function checkInvoiceEligibility($invoice, int $tenantId): array
    {
        if ($invoice->status === 'PAID') {
            return ['eligible' => false, 'reason' => 'Invoice is already paid'];
        }

        if ($invoice->status === 'CANCELLED') {
            return ['eligible' => false, 'reason' => 'Invoice is cancelled'];
        }

        if (floatval($invoice->balance_due) <= 0) {
            return ['eligible' => false, 'reason' => 'Invoice has no balance due'];
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
     * Resolve the single connected Stripe account that must serve all invoices.
     * Returns null if invoices belong to different properties or none has an active account.
     */
    private function resolveConnectedAccount($invoices): ?string
    {
        $connectedAccountId = null;

        foreach ($invoices as $invoice) {
            $propertyId = $invoice->lease?->property_id;
            if (!$propertyId) {
                return null;
            }

            $property = Property::find($propertyId);
            if (!$property || !$property->hasStripeConnected()) {
                return null;
            }

            if ($connectedAccountId === null) {
                $connectedAccountId = $property->stripe_account_id;
            } elseif ($connectedAccountId !== $property->stripe_account_id) {
                // Invoices belong to different connected accounts — not supported
                return null;
            }
        }

        return $connectedAccountId;
    }

    /**
     * Calculate processing fee based on method type.
     */
    private function calculateProcessingFee(float $baseAmount, string $paymentMethodType, $setting): float
    {
        if ($paymentMethodType === 'us_bank_account') {
            $achFlatFee = round(floatval($setting?->stripe_ach_fee ?? env('STRIPE_ACH_FEE', 5.00)), 2);
            return $achFlatFee;
        }

        // card
        $cardPct   = floatval($setting?->stripe_card_fee_percentage ?? env('STRIPE_CARD_FEE_PERCENTAGE', 2.9));
        $cardFixed = floatval($setting?->stripe_card_fee_fixed      ?? env('STRIPE_CARD_FEE_FIXED', 0.30));
        return round(($baseAmount * ($cardPct / 100)) + $cardFixed, 2);
    }

    /**
     * Send bulk-payment success emails (one per invoice or a single combined email).
     */
    private function sendBulkPaymentEmails(array $payments, array $invoices, $tenant, $metadata, float $totalCharge): void
    {
        try {
            $invoiceNumbers = implode(', ', array_column($invoices, 'invoice_number'));

            $emailData = [
                'tenant'          => $tenant,
                'tenant_name'     => $metadata['tenant_name'] ?? 'Tenant',
                'property_name'   => $metadata['property_name'] ?? 'Property',
                'unit'            => $metadata['unit'] ?? 'N/A',
                'payment_amount'  => $totalCharge,
                'invoice_number'  => $invoiceNumbers,  // combined for email subject
                'invoice_numbers' => array_column($invoices, 'invoice_number'),
                'payments'        => $payments,
                'invoices'        => $invoices,
                'payment_date'    => now()->toDateString(),
                'bulk_payment'    => true,
            ];

            Mail::to($tenant->email)->queue(new PaymentSuccessTenantMail($emailData));

            $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL'));
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new PaymentSuccessAdminMail($emailData));
            }
        } catch (Exception $e) {
            Log::error('[MultiPayment] Failed to send bulk payment emails: ' . $e->getMessage());
        }
    }
}
