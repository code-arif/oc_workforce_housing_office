<?php

namespace App\Services\Tenants;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Lease\LeaseDocument;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
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
            return [
                'success' => false,
                'message' => 'Invoice not found or unauthorized'
            ];
        }

        $lease = $invoice->lease;

        // Check if lease is signed
        $leaseDocument = LeaseDocument::where('lease_id', $lease->id)
            ->where('tenant_id', $tenantId)
            ->first();

        $leaseSigned = $leaseDocument && $leaseDocument->tenant_signed_at;

        if (!$leaseSigned) {
            return [
                'success' => false,
                'message' => 'Lease must be signed before making payments'
            ];
        }

        // Check payment eligibility
        $canMakePayment = $this->checkPaymentEligibility($invoice, $tenantId);

        if (!$canMakePayment['eligible']) {
            return [
                'success' => false,
                'message' => $canMakePayment['reason']
            ];
        }

        $assignment = $lease->assignments->where('is_current', true)->first();

        return [
            'success' => true,
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'total_amount' => $invoice->total_amount,
                    'balance_due' => $invoice->balance_due,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                    'type' => $invoice->type,
                ],
                'lease' => [
                    'id' => $lease->id,
                    'rent_amount' => $lease->rent_amount,
                    'deposit_amount' => $lease->deposit_amount,
                    'deposit_collected' => $lease->deposit_collected,
                    'property' => $lease->property->name ?? 'N/A',
                    'unit' => $assignment ? $assignment->bed->bed_label : 'N/A',
                ],
                'payment_options' => [
                    'can_adjust_rent' => true,
                    'min_rent_amount' => 0,
                    'max_rent_amount' => $invoice->balance_due,
                    'requires_deposit' => !$lease->deposit_collected && $invoice->is_first_invoice,
                    'deposit_amount' => !$lease->deposit_collected ? $lease->deposit_amount : 0,
                ],
            ]
        ];
    }

    /**
     * Check if payment is eligible
     */
    private function checkPaymentEligibility($invoice, $tenantId)
    {
        // Check if invoice is already paid
        if ($invoice->status === 'PAID') {
            return [
                'eligible' => false,
                'reason' => 'Invoice is already paid'
            ];
        }

        // Check if previous invoice is paid (sequential payment)
        $lease = $invoice->lease;
        $firstInvoice = Invoice::where('tenant_id', $tenantId)
            ->where('lease_id', $lease->id)
            ->where('type', 'RENT')
            ->orderBy('created_at', 'asc')
            ->first();

        $isFirstInvoice = $firstInvoice && $firstInvoice->id === $invoice->id;

        if (!$isFirstInvoice) {
            $previousInvoice = Invoice::where('tenant_id', $tenantId)
                ->where('lease_id', $lease->id)
                ->where('type', 'RENT')
                ->where('invoice_number', '<', $invoice->invoice_number)
                ->orderBy('invoice_number', 'desc')
                ->first();

            if ($previousInvoice && $previousInvoice->status !== 'PAID') {
                return [
                    'eligible' => false,
                    'reason' => 'Previous invoice must be paid first'
                ];
            }
        }

        return ['eligible' => true];
    }

    /**
     * Calculate payment amount
     */
    public function calculatePaymentAmount($invoiceId, $tenantId, $rentAmount = null, $includeDeposit = true)
    {
        $invoice = Invoice::with('lease')
            ->where('id', $invoiceId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'Invoice not found'
            ];
        }

        $lease = $invoice->lease;

        // Calculate rent amount
        $finalRentAmount = $rentAmount ?? $invoice->balance_due;

        // Validate rent amount
        if ($finalRentAmount < 0 || $finalRentAmount > $invoice->balance_due) {
            return [
                'success' => false,
                'message' => 'Invalid rent amount'
            ];
        }

        // Calculate deposit amount
        $depositAmount = 0;
        if ($includeDeposit && !$lease->deposit_collected && $invoice->is_first_invoice) {
            $depositAmount = $lease->deposit_amount;
        }

        $totalAmount = $finalRentAmount + $depositAmount;

        return [
            'success' => true,
            'data' => [
                'rent_amount' => $finalRentAmount,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount,
                'breakdown' => [
                    'rent' => $finalRentAmount,
                    'deposit' => $depositAmount,
                ],
            ]
        ];
    }

    /**
     * Create Stripe checkout session
     */
    public function createCheckoutSession($invoiceId, $tenantId, $rentAmount = null, $includeDeposit = true, $successUrl, $cancelUrl)
    {
        DB::beginTransaction();

        try {
            $invoice = Invoice::with([
                'lease' => function ($q) {
                    $q->with(['property', 'season', 'assignments.bed.room']);
                },
                'tenant.profile'
            ])
                ->where('id', $invoiceId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => 'Invoice not found'
                ];
            }

            // Check eligibility
            $eligibility = $this->checkPaymentEligibility($invoice, $tenantId);
            if (!$eligibility['eligible']) {
                return [
                    'success' => false,
                    'message' => $eligibility['reason']
                ];
            }

            // Calculate amounts
            $calculation = $this->calculatePaymentAmount($invoiceId, $tenantId, $rentAmount, $includeDeposit);

            if (!$calculation['success']) {
                return $calculation;
            }

            $totalAmount = $calculation['data']['total_amount'];
            $rentAmountFinal = $calculation['data']['rent_amount'];
            $depositAmountFinal = $calculation['data']['deposit_amount'];

            $lease = $invoice->lease;
            $tenant = $invoice->tenant;
            $assignment = $lease->assignments->where('is_current', true)->first();

            // Prepare line items for Stripe
            $lineItems = [];

            // Rent line item
            if ($rentAmountFinal > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $rentAmountFinal * 100, // Stripe uses cents
                        'product_data' => [
                            'name' => 'Rent Payment',
                            'description' => 'Invoice ' . $invoice->invoice_number . ' - ' . ($lease->property->name ?? 'Property'),
                        ],
                    ],
                    'quantity' => 1,
                ];
            }

            // Deposit line item
            if ($depositAmountFinal > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $depositAmountFinal * 100,
                        'product_data' => [
                            'name' => 'Security Deposit',
                            'description' => 'One-time security deposit',
                        ],
                    ],
                    'quantity' => 1,
                ];
            }

            // Prepare metadata for session
            $metadata = [
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenantId,
                'lease_id' => $lease->id,
                'rent_amount' => $rentAmountFinal,
                'deposit_amount' => $depositAmountFinal,
                'total_amount' => $totalAmount,
                'invoice_number' => $invoice->invoice_number,
                'property_name' => $lease->property->name ?? 'N/A',
                'unit' => $assignment ? $assignment->bed->bed_label : 'N/A',
                'lease_start_date' => $lease->start_date,
                'lease_end_date' => $lease->end_date,
            ];

            // Create Stripe checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'customer_email' => $tenant->email,
                'client_reference_id' => $invoice->id,
                'metadata' => $metadata,
            ]);

            DB::commit();

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'total_amount' => $totalAmount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stripe checkout session creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create payment session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment after Stripe redirect
     */
    public function verifyPayment($sessionId, $tenantId)
    {
        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return [
                    'success' => false,
                    'message' => 'Payment not completed'
                ];
            }

            $metadata = $session->metadata;
            $invoiceId = $metadata['invoice_id'];

            // Process the payment
            $result = $this->processPayment($invoiceId, $tenantId, $metadata, $session);

            return $result;
        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Payment verification failed'
            ];
        }
    }

    /**
     * Process payment after successful Stripe payment
     */
    private function processPayment($invoiceId, $tenantId, $metadata, $session)
    {
        DB::beginTransaction();

        try {
            $invoice = Invoice::with('lease')->findOrFail($invoiceId);
            $lease = $invoice->lease;

            if ($invoice->tenant_id !== $tenantId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized'
                ];
            }

            $rentAmount = floatval($metadata['rent_amount']);
            $depositAmount = floatval($metadata['deposit_amount']);
            $totalAmount = floatval($metadata['total_amount']);

            $bedId = $lease->assignments()->where('is_current', true)->first()->bed_id ?? null;

            // Create deposit payment if applicable
            if ($depositAmount > 0) {
                $depositInvoice = Invoice::where('lease_id', $lease->id)
                    ->where('tenant_id', $tenantId)
                    ->where('type', 'DEPOSIT')
                    ->where('status', '!=', 'PAID')
                    ->first();

                if ($depositInvoice) {
                    $depositPayment = Payment::create([
                        'invoice_id' => $depositInvoice->id,
                        'tenant_id' => $tenantId,
                        'lease_id' => $lease->id,
                        'bed_id' => $bedId,
                        'payment_number' => 'PAY-' . uniqid(),
                        'amount' => $depositAmount,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => 'stripe',
                        'reference_number' => $session->payment_intent,
                        'gateway_transaction_id' => $session->id,
                        'payment_type' => 'deposit',
                        'paid_by' => 'tenant',
                        'note' => 'Security deposit payment via Stripe',
                        'metadata' => [
                            'stripe_session_id' => $session->id,
                            'stripe_payment_intent' => $session->payment_intent,
                        ]
                    ]);

                    // Update deposit invoice
                    $depositInvoice->update([
                        'paid_amount' => $depositAmount,
                        'balance_due' => 0,
                        'status' => 'PAID',
                        'paid_at' => now(),
                    ]);

                    // Mark deposit as collected
                    $lease->update(['deposit_collected' => true]);

                    // Create transaction
                    Transaction::create([
                        'tenant_id' => $tenantId,
                        'bed_id' => $bedId,
                        'lease_id' => $lease->id,
                        'invoice_id' => $depositInvoice->id,
                        'payment_id' => $depositPayment->id,
                        'transaction_number' => 'TXN-' . uniqid(),
                        'type' => 'payment',
                        'entry_type' => 'credit',
                        'amount' => $depositAmount,
                        'transaction_date' => now()->toDateString(),
                        'description' => 'Security deposit payment for Invoice ' . $invoice->invoice_number,
                        'metadata' => [
                            'payment_method' => 'stripe',
                            'stripe_session_id' => $session->id,
                        ]
                    ]);
                }
            }

            // Create rent payment
            if ($rentAmount > 0) {
                $rentPayment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenantId,
                    'lease_id' => $lease->id,
                    'bed_id' => $bedId,
                    'payment_number' => 'PAY-' . uniqid(),
                    'amount' => $rentAmount,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => 'stripe',
                    'reference_number' => $session->payment_intent,
                    'gateway_transaction_id' => $session->id,
                    'payment_type' => ($rentAmount >= $invoice->balance_due) ? 'full' : 'partial',
                    'paid_by' => 'tenant',
                    'note' => 'Rent payment via Stripe',
                    'metadata' => [
                        'stripe_session_id' => $session->id,
                        'stripe_payment_intent' => $session->payment_intent,
                    ]
                ]);

                // Update rent invoice
                $newPaidAmount = ($invoice->paid_amount ?? 0) + $rentAmount;
                $newBalance = $invoice->total_amount - $newPaidAmount;

                $status = 'PARTIAL';
                $paidAt = null;

                if ($newBalance <= 0) {
                    $status = 'PAID';
                    $paidAt = now();
                    $newBalance = 0;
                }

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'balance_due' => $newBalance,
                    'status' => $status,
                    'paid_at' => $paidAt,
                ]);

                // Create transaction
                Transaction::create([
                    'tenant_id' => $tenantId,
                    'bed_id' => $bedId,
                    'lease_id' => $lease->id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $rentPayment->id,
                    'transaction_number' => 'TXN-' . uniqid(),
                    'type' => 'payment',
                    'entry_type' => 'credit',
                    'amount' => $rentAmount,
                    'transaction_date' => now()->toDateString(),
                    'description' => 'Rent payment for Invoice ' . $invoice->invoice_number,
                    'metadata' => [
                        'payment_method' => 'stripe',
                        'stripe_session_id' => $session->id,
                    ]
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'payment' => [
                    'total_amount' => $totalAmount,
                    'rent_amount' => $rentAmount,
                    'deposit_amount' => $depositAmount,
                    'payment_date' => now()->toDateString(),
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'status' => $invoice->fresh()->status,
                    'balance_due' => $invoice->fresh()->balance_due,
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Payment processing failed'
            ];
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook($request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret');

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Webhook signature verification failed'
            ];
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                // Process the payment
                $metadata = $session->metadata;
                $this->processPayment(
                    $metadata['invoice_id'],
                    $metadata['tenant_id'],
                    $metadata,
                    $session
                );
                break;

            default:
                Log::info('Unhandled webhook event type: ' . $event->type);
        }

        return ['success' => true];
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($tenantId)
    {
        return Payment::with(['invoice', 'lease.property'])
            ->where('tenant_id', $tenantId)
            ->where('payment_method', 'stripe')
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
                    'invoice_number' => $payment->invoice->invoice_number,
                    'property_name' => $payment->lease->property->name ?? 'N/A',
                    'reference_number' => $payment->reference_number,
                ];
            });
    }
}
