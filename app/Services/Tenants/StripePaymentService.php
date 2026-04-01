<?php

namespace App\Services\Tenants;

use Exception;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Lease\LeaseDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Tenant\Payment\PaymentSuccessAdminMail;
use App\Mail\Tenant\Payment\PaymentSuccessTenantMail;

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

        // Check if lease is signed
        $leaseDocument = LeaseDocument::where('lease_id', $invoice->lease_id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$leaseDocument || !$leaseDocument->tenant_signed_at) {
            return [
                'eligible' => false,
                'reason' => 'Lease must be signed before making payments'
            ];
        }

        // Find first unpaid invoice for this tenant
        $firstUnpaidInvoice = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        // Only the first unpaid invoice can be paid
        if ($firstUnpaidInvoice && $firstUnpaidInvoice->id !== $invoice->id) {
            return [
                'eligible' => false,
                'reason' => 'Previous invoice must be paid first (Invoice #' . $firstUnpaidInvoice->invoice_number . ')'
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Create Stripe checkout session
     */
    public function createCheckoutSession($invoiceId, $tenantId)
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
                return [
                    'success' => false,
                    'message' => 'Invoice not found'
                ];
            }

            // Check eligibility
            $eligibility = $this->checkPaymentEligibility($invoice, $tenantId);
            if (!$eligibility['eligible']) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => $eligibility['reason']
                ];
            }

            $lease = $invoice->lease;
            $tenant = $invoice->tenant;
            $assignment = $lease->assignments->where('is_current', true)->first();

            // Calculate payment amount from invoice balance_due
            $paymentAmount = floatval($invoice->balance_due);

            if ($paymentAmount <= 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Invoice has no balance due'
                ];
            }

            // Prepare line items for Stripe
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $paymentAmount * 100, // Stripe uses cents
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

            // Prepare tenant metadata
            $tenantProfile = $tenant->profile;
            $tenantAddress = $tenant->address;

            // Prepare metadata for session
            $metadata = [
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenantId,
                'lease_id' => $lease->id,
                'payment_amount' => $paymentAmount,
                'invoice_number' => $invoice->invoice_number,
                'invoice_type' => $invoice->type,

                // Tenant Information
                'tenant_email' => $tenant->email,
                'tenant_name' => ($tenantProfile ? trim($tenantProfile->first_name . ' ' . ($tenantProfile->middle_name ?? '') . ' ' . ($tenantProfile->last_name ?? '')) : 'N/A'),
                'tenant_phone' => $tenantProfile->phone ?? 'N/A',

                // Tenant Address
                'tenant_address' => $tenantAddress->address ?? 'N/A',
                'tenant_city' => $tenantAddress->city ?? 'N/A',
                'tenant_state' => $tenantAddress->state ?? 'N/A',
                'tenant_zip' => $tenantAddress->zip ?? 'N/A',
                'tenant_country' => $tenantAddress->country ?? 'USA',

                // Lease Information
                'property_name' => $lease->property->name ?? 'N/A',
                'property_address' => $lease->property->address ?? 'N/A',
                'unit' => $assignment ? $assignment->bed->bed_label : 'N/A',
                'lease_start_date' => $lease->start_date->format('Y-m-d'),
                'lease_end_date' => $lease->end_date->format('Y-m-d'),
                'rent_amount' => $lease->rent_amount,
                'deposit_amount' => $lease->deposit_amount,
                'payment_frequency' => $lease->payment_frequency,
            ];

            // Get success and cancel URLs from env
            $successUrl = config('services.stripe.success_url', env('STRIPE_SUCCESS_URL'));
            $cancelUrl = config('services.stripe.cancel_url', env('STRIPE_CANCEL_URL'));

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
            Log::error('Stripe checkout session creation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create payment session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment after Stripe redirect (for localhost testing)
     */
    public function verifyPayment($sessionId)
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
            $tenantId = $metadata['tenant_id'];

            // Process the payment (same as webhook)
            $result = $this->processPayment($metadata, $session);

            return $result;
        } catch (Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process payment after successful Stripe payment
     */
    private function processPayment($metadata, $session)
    {
        DB::beginTransaction();

        try {
            $invoiceId = $metadata['invoice_id'];
            $tenantId = $metadata['tenant_id'];
            $paymentAmount = floatval($metadata['payment_amount']);

            $invoice = Invoice::with(['lease', 'tenant.profile'])->findOrFail($invoiceId);
            $lease = $invoice->lease;
            $tenant = $invoice->tenant;

            if ($invoice->tenant_id != $tenantId) {
                throw new Exception('Unauthorized payment attempt');
            }

            // Check if payment already processed
            $existingPayment = Payment::where('gateway_transaction_id', $session->id)->first();
            if ($existingPayment) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Payment already processed'
                ];
            }

            $bedId = $lease->assignments()->where('is_current', true)->first()->bed_id ?? null;

            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenantId,
                'lease_id' => $lease->id,
                'bed_id' => $bedId,
                'payment_number' => 'PAY-' . strtoupper(uniqid()),
                'amount' => $paymentAmount,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'stripe',
                'reference_number' => $session->payment_intent,
                'gateway_transaction_id' => $session->id,
                'payment_type' => $invoice->type === 'DEPOSIT' ? 'deposit' : 'rent',
                'paid_by' => 'tenant',
                'note' => sprintf(
                    '%s payment via Stripe - Invoice %s',
                    $invoice->type === 'DEPOSIT' ? 'Security deposit' : 'Rent',
                    $invoice->invoice_number
                ),
                'metadata' => [
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent' => $session->payment_intent,
                    'tenant_name' => $metadata['tenant_name'] ?? 'N/A',
                    'property_name' => $metadata['property_name'] ?? 'N/A',
                ]
            ]);

            // Update invoice
            $newPaidAmount = floatval($invoice->paid_amount) + $paymentAmount;
            $newBalance = floatval($invoice->total_amount) - $newPaidAmount;

            $status = 'PARTIAL';
            $paidAt = null;

            if ($newBalance <= 0.01) { // Allow small rounding differences
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

            // Mark deposit as collected if this was a deposit payment
            if ($invoice->type === 'DEPOSIT' && $status === 'PAID') {
                $lease->update(['deposit_collected' => true]);
            }

            // Create transaction
            Transaction::create([
                'tenant_id' => $tenantId,
                'bed_id' => $bedId,
                'lease_id' => $lease->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'transaction_number' => 'TXN-' . strtoupper(uniqid()),
                'type' => 'payment',
                'entry_type' => 'credit',
                'amount' => $paymentAmount,
                'transaction_date' => now()->toDateString(),
                'description' => sprintf(
                    '%s payment for Invoice %s - %s',
                    $invoice->type === 'DEPOSIT' ? 'Deposit' : 'Rent',
                    $invoice->invoice_number,
                    $metadata['property_name'] ?? 'Property'
                ),
                'metadata' => [
                    'payment_method' => 'stripe',
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent' => $session->payment_intent,
                ]
            ]);

            DB::commit();

            // Send emails to both tenant and admin
            $this->sendPaymentEmails($payment, $invoice, $tenant, $metadata);

            return [
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => $paymentAmount,
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
                'session_id' => $session->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send payment success emails
     */
    private function sendPaymentEmails($payment, $invoice, $tenant, $metadata)
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

            // Send email to tenant
            Mail::to($tenant->email)->queue(new PaymentSuccessTenantMail($emailData));



            // Send email to admin (get from config)
            $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL'));
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new PaymentSuccessAdminMail($emailData));
            }

            Log::info('Payment success emails sent', [
                'payment_id' => $payment->id,
                'tenant_email' => $tenant->email,
                'admin_email' => $adminEmail ?? 'not configured'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send payment emails: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? 'unknown'
            ]);
            // Don't throw exception - email failure shouldn't break payment processing
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
        } catch (Exception $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Webhook signature verification failed'
            ];
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                Log::info('Checkout session completed', [
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status
                ]);

                if ($session->payment_status === 'paid') {
                    $metadata = $session->metadata;
                    $result = $this->processPayment($metadata, $session);

                    if (!$result['success']) {
                        Log::error('Webhook payment processing failed', [
                            'session_id' => $session->id,
                            'error' => $result['message']
                        ]);
                    }
                }
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
            });
    }
}
