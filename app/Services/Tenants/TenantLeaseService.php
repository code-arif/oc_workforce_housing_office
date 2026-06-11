<?php

namespace App\Services\Tenants;

use App\Models\Lease;
use App\Models\Invoice;
use App\Models\Lease\LeaseDocument;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TenantLeaseService
{
    /**
     * Get tenant dashboard data
     */
    public function getDashboardData($tenantId)
    {
        // Get unsigned leases (pending signature)
        $unsignedLeases = $this->getUnsignedLeases($tenantId);

        // Get signed/active leases
        $signedLeases = $this->getSignedLeases($tenantId);

        // Get invoices with payment blocking logic
        $invoices = $this->getInvoicesWithPaymentStatus($tenantId);

        // Get recent payments
        $recentPayments = $this->getRecentPayments($tenantId, 5);

        // Calculate statistics
        $statistics = [
            'active_leases_count' => Lease::where('tenant_id', $tenantId)
                ->where('status', 'ACTIVE')
                ->count(),
            'pending_leases_count' => Lease::where('tenant_id', $tenantId)
                ->whereIn('status', ['PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
                ->count(),
            'total_unpaid_amount' => Invoice::where('tenant_id', $tenantId)
                ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->sum('balance_due'),
        ];

        return [
            'unsigned_leases' => $unsignedLeases,
            'signed_leases' => $signedLeases,
            'invoices' => $invoices,
            'recent_payments' => $recentPayments,
            'statistics' => $statistics,
        ];
    }

    /**
     * Get unsigned leases (requiring tenant signature)
     */
    private function getUnsignedLeases($tenantId)
    {
        return Lease::with([
            'property:id,name,address',
            'assignments' => function ($q) {
                $q->where('is_current', true)
                    ->with('bed:id,bed_label,bed_number,room_id');
            },
            'documents' => function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                    ->with('template:id,name');
            }
        ])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($lease) {
                $document = $lease->documents->first();
                $assignment = $lease->assignments->first();

                return [
                    'id' => $lease->id,
                    'status' => $lease->status,
                    'start_date' => $lease->start_date,
                    'end_date' => $lease->end_date,
                    'rent_amount' => $lease->rent_amount,
                    'deposit_amount' => $lease->deposit_amount,
                    'payment_frequency' => $lease->payment_frequency,
                    'property' => $lease->property,
                    'unit' => $assignment ? $assignment->bed->bed_label : null,
                    'requires_signature' => $lease->status === 'PENDING_TENANT_SIGN',
                    'document' => $document ? [
                        'id' => $document->id,
                        'template_name' => $document->template ? $document->template->name : null,
                        'tenant_signed_at' => $document->tenant_signed_at,
                        'admin_signed_at' => $document->admin_signed_at,
                    ] : null,
                ];
            });
    }

    /**
     * Get signed/active leases
     */
    private function getSignedLeases($tenantId)
    {
        return Lease::with([
            'property:id,name,address',
            'assignments' => function ($q) {
                $q->where('is_current', true)
                    ->with('bed:id,bed_label,bed_number,room_id');
            },
            'documents' => function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            }
        ])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['ACTIVE', 'COMPLETED', 'TERMINATED'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($lease) {
                $document = $lease->documents->first();
                $assignment = $lease->assignments->first();
                $daysRemaining = now()->diffInDays($lease->end_date, false);

                return [
                    'id' => $lease->id,
                    'status' => $lease->status,
                    'start_date' => $lease->start_date,
                    'end_date' => $lease->end_date,
                    'rent_amount' => $lease->rent_amount,
                    'deposit_amount' => $lease->deposit_amount,
                    'deposit_collected' => $lease->deposit_collected,
                    'payment_frequency' => $lease->payment_frequency,
                    'property' => $lease->property,
                    'unit' => $assignment ? $assignment->bed->bed_label : null,
                    'days_remaining' => max(0, $daysRemaining),
                    'is_expired' => $daysRemaining < 0,
                    'document' => $document ? [
                        'tenant_signed_at' => $document->tenant_signed_at,
                        'admin_signed_at' => $document->admin_signed_at,
                        'fully_signed' => $document->tenant_signed_at && $document->admin_signed_at,
                    ] : null,
                ];
            });
    }

    /**
     * Get invoices with payment blocking logic
     */
    // private function getInvoicesWithPaymentStatus($tenantId)
    // {
    //     $invoices = Invoice::with([
    //         'lease' => function ($q) {
    //             $q->with('property:id,name');
    //         },
    //         'payments' => function ($q) {
    //             $q->orderBy('payment_date', 'desc');
    //         }
    //     ])
    //         ->where('tenant_id', $tenantId)
    //         ->orderBy('due_date', 'asc')
    //         ->get();

    //     return $invoices->map(function ($invoice, $index) use ($invoices, $tenantId) {
    //         // Check if lease is signed
    //         $leaseDocument = LeaseDocument::where('lease_id', $invoice->lease_id)
    //             ->where('tenant_id', $tenantId)
    //             ->first();

    //         $leaseSigned = $leaseDocument && $leaseDocument->tenant_signed_at;

    //         // Determine if this is the first invoice
    //         $firstInvoice = Invoice::where('tenant_id', $tenantId)
    //             ->where('lease_id', $invoice->lease_id)
    //             ->where('type', 'RENT')
    //             ->orderBy('created_at', 'asc')
    //             ->first();

    //         $isFirstInvoice = $firstInvoice && $firstInvoice->id === $invoice->id;

    //         // Check if previous invoice is paid (for sequential payment)
    //         $canPayment = false;
    //         $blockReason = null;

    //         if (!$leaseSigned) {
    //             $blockReason = 'Lease must be signed before making payments';
    //         } elseif ($isFirstInvoice) {
    //             $canPayment = true;
    //         } else {
    //             // Get previous invoice
    //             $previousInvoice = Invoice::where('tenant_id', $tenantId)
    //                 ->where('lease_id', $invoice->lease_id)
    //                 ->where('type', 'RENT')
    //                 ->where('invoice_number', '<', $invoice->invoice_number)
    //                 ->orderBy('invoice_number', 'desc')
    //                 ->first();

    //             if (!$previousInvoice) {
    //                 $canPayment = true;
    //             } elseif ($previousInvoice->status === 'PAID') {
    //                 $canPayment = true;
    //             } else {
    //                 $blockReason = 'Previous invoice must be paid first';
    //             }
    //         }

    //         // Override: Can always pay if status is UNPAID, PARTIAL, or OVERDUE and lease is signed
    //         if ($leaseSigned && in_array($invoice->status, ['UNPAID', 'PARTIAL', 'OVERDUE'])) {
    //             if (!$blockReason || $blockReason === 'Previous invoice must be paid first') {
    //                 // Only block if explicitly told by previous invoice logic
    //                 if ($blockReason === 'Previous invoice must be paid first') {
    //                     $canPayment = false;
    //                 } else {
    //                     $canPayment = true;
    //                 }
    //             }
    //         }

    //         return [
    //             'id' => $invoice->id,
    //             'invoice_number' => $invoice->invoice_number,
    //             'type' => $invoice->type,
    //             'amount' => $invoice->amount,
    //             'total_amount' => $invoice->total_amount,
    //             'paid_amount' => $invoice->paid_amount,
    //             'balance_due' => $invoice->balance_due,
    //             'due_date' => $invoice->due_date,
    //             'status' => $invoice->status,
    //             'is_overdue' => $invoice->status === 'OVERDUE' || ($invoice->due_date < now() && $invoice->status !== 'PAID'),
    //             'lease' => [
    //                 'id' => $invoice->lease->id,
    //                 'property_name' => $invoice->lease->property->name ?? 'N/A',
    //                 'is_signed' => $leaseSigned,
    //             ],
    //             'can_make_payment' => $canPayment,
    //             'payment_blocked_reason' => $blockReason,
    //             'is_first_invoice' => $isFirstInvoice,
    //             'includes_deposit' => $invoice->includes_deposit,
    //             'recent_payment' => $invoice->payments->first() ? [
    //                 'amount' => $invoice->payments->first()->amount,
    //                 'payment_date' => $invoice->payments->first()->payment_date,
    //                 'payment_method' => $invoice->payments->first()->payment_method,
    //             ] : null,
    //         ];
    //     });
    // }

    /**
     * Get invoices with payment blocking logic
     */
    private function getInvoicesWithPaymentStatus($tenantId)
    {
        $invoices = Invoice::with([
            'lease' => function ($q) {
                $q->with('property:id,name');
            },
            'payments' => function ($q) {
                $q->orderBy('payment_date', 'desc');
            }
        ])
            ->where('tenant_id', $tenantId)
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc') // Ensure consistent ordering
            ->get();

        // Find the first unpaid invoice (the one that should be payable)
        $firstUnpaidInvoiceId = null;

        foreach ($invoices as $invoice) {
            if (in_array($invoice->status, ['UNPAID', 'PARTIAL', 'OVERDUE'])) {
                $firstUnpaidInvoiceId = $invoice->id;
                break; // Stop at first unpaid
            }
        }

        return $invoices->map(function ($invoice) use ($tenantId, $firstUnpaidInvoiceId) {
            // Check if lease is signed
            $leaseDocument = LeaseDocument::where('lease_id', $invoice->lease_id)
                ->where('tenant_id', $tenantId)
                ->first();

            $leaseSigned = $leaseDocument && $leaseDocument->tenant_signed_at;

            // Determine if this is the first invoice
            $firstInvoice = Invoice::where('tenant_id', $tenantId)
                ->where('lease_id', $invoice->lease_id)
                ->where('type', 'RENT')
                ->orderBy('created_at', 'asc')
                ->first();

            $isFirstInvoice = $firstInvoice && $firstInvoice->id === $invoice->id;

            // Payment logic
            // $canPayment = false;
            $blockReason = null;

            if (!$leaseSigned) {
                $blockReason = 'Lease must be signed before making payments';
            } elseif ($invoice->status === 'PAID') {
                $blockReason = 'Invoice already paid';
            } else {
                $blockReason = 'Previous invoice must be paid first';
            }

            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'type' => $invoice->type,
                'amount' => $invoice->amount,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'balance_due' => $invoice->balance_due,
                'due_date' => $invoice->due_date,
                'status' => $invoice->status,
                'is_overdue' => $invoice->status === 'OVERDUE' || ($invoice->due_date < now() && $invoice->status !== 'PAID'),
                'lease' => [
                    'id' => $invoice->lease->id,
                    'property_name' => $invoice->lease->property->name ?? 'N/A',
                    'is_signed' => $leaseSigned,
                ],
                // 'can_make_payment' => $canPayment,
                'payment_blocked_reason' => $blockReason,
                'is_first_invoice' => $isFirstInvoice,
                'includes_deposit' => $invoice->includes_deposit,
                'recent_payment' => $invoice->payments->first() ? [
                    'amount' => $invoice->payments->first()->amount,
                    'payment_date' => $invoice->payments->first()->payment_date,
                    'payment_method' => $invoice->payments->first()->payment_method,
                ] : null,
            ];
        });
    }

    /**
     * Get tenant leases
     */
    public function getTenantLeases($tenantId)
    {
        return Lease::with([
            'property:id,name,address',
            'assignments' => function ($q) {
                $q->where('is_current', true)->with('bed:id,bed_label,room_id');
            },
            'documents' => function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            },
            'invoices'
        ])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($lease) {
                $document = $lease->documents->first();
                return [
                    'id' => $lease->id,
                    'status' => $lease->status,
                    'start_date' => $lease->start_date,
                    'end_date' => $lease->end_date,
                    'rent_amount' => $lease->rent_amount,
                    'deposit_amount' => $lease->deposit_amount,
                    'deposit_collected' => $lease->deposit_collected,
                    'payment_frequency' => $lease->payment_frequency,
                    'property' => $lease->property,
                    'unit' => $lease->assignments->first() ? $lease->assignments->first()->bed->bed_label : null,
                    'is_signed' => $document && $document->tenant_signed_at ? true : false,
                    'total_invoices' => $lease->invoices->count(),
                    'unpaid_invoices' => $lease->invoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->count(),
                ];
            });
    }

    /**
     * Get lease details
     */
    public function getLeaseDetails($leaseId, $tenantId)
    {
        $lease = Lease::with([
            'property',
            'season',
            'assignments.bed.room',
            'documents' => function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->with('template');
            },
            'invoices.payments',
            'paymentSchedules'
        ])
            ->where('id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$lease) {
            return null;
        }

        $document = $lease->documents->first();
        $assignment = $lease->assignments->where('is_current', true)->first();

        return [
            'id' => $lease->id,
            'status' => $lease->status,
            'start_date' => $lease->start_date,
            'end_date' => $lease->end_date,
            'rent_amount' => $lease->rent_amount,
            'deposit_amount' => $lease->deposit_amount,
            'deposit_collected' => $lease->deposit_collected,
            'payment_frequency' => $lease->payment_frequency,
            'notes' => $lease->notes,
            'property' => $lease->property,
            'season' => $lease->season,
            'unit' => $assignment ? [
                'bed' => $assignment->bed,
                'room' => $assignment->bed->room,
                'move_in_date' => $assignment->actual_move_in,
            ] : null,
            'document' => $document ? [
                'id' => $document->id,
                'template_name' => $document->template->name ?? null,
                'tenant_signed_at' => $document->tenant_signed_at,
                'admin_signed_at' => $document->admin_signed_at,
                'fully_signed' => $document->tenant_signed_at && $document->admin_signed_at,
            ] : null,
            'invoices' => $lease->invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                    'type' => $invoice->type,
                ];
            }),
            'payment_schedules' => $lease->paymentSchedules,
        ];
    }

    /**
     * Get tenant invoices
     */
    public function getTenantInvoices($tenantId, $status = null)
    {
        $query = Invoice::with([
            'lease.property',
            'payments'
        ])
            ->where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('due_date', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'amount' => $invoice->amount,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                    'due_date' => $invoice->due_date,
                    'status' => $invoice->status,
                    'is_overdue' => $invoice->due_date < now() && $invoice->status !== 'PAID',
                    'property_name' => $invoice->lease->property->name ?? 'N/A',
                    'payment_count' => $invoice->payments->count(),
                ];
            });
    }

    /**
     * Get invoice details
     */
    public function getInvoiceDetails($invoiceId, $tenantId)
    {
        $invoice = Invoice::with([
            'lease.property',
            'lease.assignments.bed',
            'payments' => function ($q) {
                $q->orderBy('payment_date', 'desc');
            }
        ])
            ->where('id', $invoiceId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$invoice) {
            return null;
        }

        $assignment = $invoice->lease->assignments->where('is_current', true)->first();

        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'type' => $invoice->type,
            'amount' => $invoice->amount,
            'total_amount' => $invoice->total_amount,
            'paid_amount' => $invoice->paid_amount,
            'balance_due' => $invoice->balance_due,
            'due_date' => $invoice->due_date,
            'issue_date' => $invoice->issue_date,
            'status' => $invoice->status,
            'is_overdue' => $invoice->due_date < now() && $invoice->status !== 'PAID',
            'notes' => $invoice->notes,
            'lease' => [
                'id' => $invoice->lease->id,
                'property' => $invoice->lease->property,
                'unit' => $assignment ? $assignment->bed->bed_label : null,
                'rent_amount' => $invoice->lease->rent_amount,
                'deposit_amount' => $invoice->lease->deposit_amount,
            ],
            'payments' => $invoice->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'reference_number' => $payment->reference_number,
                    'note' => $payment->note,
                ];
            }),
        ];
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($tenantId, $limit = null)
    {
        $query = Payment::with([
            'invoice',
            'lease.property'
        ])
            ->where('tenant_id', $tenantId)
            ->orderBy('payment_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'payment_type' => $payment->payment_type,
                'note' => $payment->note,
                'invoice' => [
                    'id' => $payment->invoice->id,
                    'invoice_number' => $payment->invoice->invoice_number,
                ],
                'property_name' => $payment->lease->property->name ?? 'N/A',
            ];
        });
    }

    /**
     * Get recent payments
     */
    private function getRecentPayments($tenantId, $limit = 5)
    {
        return $this->getPaymentHistory($tenantId, $limit);
    }

    /**
     * Get transactions
     */
    public function getTransactions($tenantId)
    {
        return Transaction::with([
            'lease.property',
            'invoice',
            'payment'
        ])
            ->where('tenant_id', $tenantId)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type,
                    'entry_type' => $transaction->entry_type,
                    'amount' => $transaction->amount,
                    'transaction_date' => $transaction->transaction_date,
                    'description' => $transaction->description,
                    'notes' => $transaction->notes,
                    'property_name' => $transaction->lease && $transaction->lease->property
                        ? $transaction->lease->property->name
                        : 'N/A',
                    'invoice_number' => $transaction->invoice ? $transaction->invoice->invoice_number : null,
                ];
            });
    }

    /**
     * Check if tenant has active lease
     */
    public function tenantHasActiveLease($tenantId)
    {
        return Lease::where('tenant_id', $tenantId)
            ->where('status', 'ACTIVE')
            ->exists();
    }
}
