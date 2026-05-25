<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'lease.property',
            'lease.tenant.profile',
            'lease.assignments.bed',
            'items.item',
            'payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ])->findOrFail($id);

        $lease = $invoice->lease;

        // Determine if this is the first invoice
        $firstInvoice = $lease->invoices()
            ->where('type', 'RENT')
            ->orderBy('created_at', 'asc')
            ->first();

        $isFirstInvoice = $firstInvoice && $firstInvoice->id === $invoice->id;

        $depositInvoice = Invoice::where('invoice_number', $invoice->invoice_number)
            ->where('type', 'DEPOSIT')
            ->where('status', '!=', 'PAID')
            ->first();

        // Check if previous invoice is paid (for non-first invoices)
        $canMakePayment = false;
        if ($isFirstInvoice) {
            $canMakePayment = true;
        } else {
            // Get the previous invoice
            $previousInvoice = $lease->invoices()
                ->where('type', 'RENT')
                ->where('invoice_number', '<', $invoice->invoice_number)
                ->orderBy('invoice_number', 'desc')
                ->first();

            // Can make payment if there's no previous invoice or previous is paid
            $canMakePayment = !$previousInvoice || $previousInvoice->status === 'PAID' || $invoice->type == 'ITEM_SALE';
        }

        // Payment blocked on CANCELLED invoices
        if ($invoice->status === 'CANCELLED') {
            $canMakePayment = false;
        }

        // Calculate totals - use stored values if available
        $totalDue = $invoice->total_amount;

        // If the total_amount is not set, calculate it
        if (!$totalDue || $totalDue == 0) {
            $totalDue = $invoice->amount;
        }

        $totalPaid = $invoice->paid_amount ?? $invoice->payments->sum('amount');
        $balanceDue = $invoice->balance_due ?? ($totalDue - $totalPaid);

        $isSuperAdmin = $this->isSuperAdmin(Auth::id());
        $hasOnlyCashPayments = $invoice->payments->isNotEmpty() && $invoice->payments->every(function ($payment) {
            return $payment->payment_method === 'cash' || $payment->payment_method === 'check';
        });
        $canCancelPaidCash = $isSuperAdmin && $invoice->status === 'PAID' && $hasOnlyCashPayments;

        // Items list for edit modal (ITEM_SALE invoices)
        $availableItems = Item::where('status', true)->get();

        return view('backend.layouts.leases.invoice.show', compact(
            'invoice',
            'lease',
            'isFirstInvoice',
            'canMakePayment',
            'totalDue',
            'totalPaid',
            'balanceDue',
            'depositInvoice',
            'availableItems',
            'canCancelPaidCash'
        ));
    }

    /**
     * Update the specified invoice (amounts, due date, notes, line items).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount'               => 'sometimes|numeric|min:0',
            'due_date'             => 'sometimes|date',
            'notes'                => 'nullable|string|max:2000',
            'items'                => 'sometimes|array|min:1',
            'items.*.item_id'      => 'nullable|exists:items,id',
            'items.*.item_name'    => 'required_with:items|string|max:255',
            'items.*.description'  => 'nullable|string|max:1000',
            'items.*.quantity'     => 'required_with:items|integer|min:1',
            'items.*.rate'         => 'required_with:items|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::with(['items', 'lease'])->findOrFail($id);

            if ($invoice->status === 'CANCELLED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit a cancelled/voided invoice.',
                ], 422);
            }

            $lease       = $invoice->lease;
            $updateData  = [];
            $changeLog   = [];

            // Due date
            if ($request->filled('due_date') && $request->due_date !== $invoice->due_date->format('Y-m-d')) {
                $changeLog['due_date'] = ['from' => $invoice->due_date->format('Y-m-d'), 'to' => $request->due_date];
                $updateData['due_date'] = $request->due_date;
            }

            // Notes
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            // Line-item recalculation (ITEM_SALE invoices)
            if ($invoice->type === 'ITEM_SALE' && $request->has('items')) {
                $invoice->items()->delete();

                $newTotal = 0;
                foreach ($request->items as $itemData) {
                    $lineAmount = (int) $itemData['quantity'] * (float) $itemData['rate'];
                    $newTotal  += $lineAmount;

                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'item_id'     => $itemData['item_id'] ?? null,
                        'item_name'   => $itemData['item_name'],
                        'description' => $itemData['description'] ?? null,
                        'quantity'    => (int) $itemData['quantity'],
                        'rate'        => (float) $itemData['rate'],
                        'amount'      => $lineAmount,
                    ]);
                }

                $changeLog['total_amount'] = ['from' => $invoice->total_amount, 'to' => $newTotal];
                $updateData['amount']      = $newTotal;
                $updateData['total_amount'] = $newTotal;
                $updateData['balance_due'] = max(0, $newTotal - ($invoice->paid_amount ?? 0));

            // Amount adjustment (non-ITEM_SALE invoices)
            } elseif ($request->filled('amount') && $invoice->type !== 'ITEM_SALE') {
                $newAmount = (float) $request->amount;
                $newTotal  = $newAmount;

                // Preserve deposit component for first invoices
                if ($invoice->is_first_invoice && $invoice->includes_deposit && !$lease->deposit_collected) {
                    $newTotal += (float) $lease->deposit_amount;
                }

                $changeLog['amount']       = ['from' => $invoice->amount,       'to' => $newAmount];
                $changeLog['total_amount'] = ['from' => $invoice->total_amount, 'to' => $newTotal];

                $updateData['amount']       = $newAmount;
                $updateData['total_amount'] = $newTotal;
                $updateData['balance_due']  = max(0, $newTotal - ($invoice->paid_amount ?? 0));
            }

            // Recalculate status when balance/amounts changed
            if (isset($updateData['balance_due'])) {
                $paid = $invoice->paid_amount ?? 0;
                if ($updateData['balance_due'] <= 0 && $paid > 0) {
                    $updateData['status']  = 'PAID';
                    $updateData['paid_at'] = $invoice->paid_at ?? now();
                } elseif ($paid > 0) {
                    $updateData['status'] = 'PARTIAL';
                } else {
                    $checkDate = $updateData['due_date'] ?? $invoice->due_date->format('Y-m-d');
                    $updateData['status'] = (strtotime($checkDate) < time()) ? 'OVERDUE' : 'UNPAID';
                }
            }

            $invoice->update($updateData);

            // Audit transaction
            if (!empty($changeLog)) {
                $bedId = $lease->assignments()->where('is_current', true)->value('bed_id');
                Transaction::create([
                    'tenant_id'          => $invoice->tenant_id,
                    'bed_id'             => $bedId,
                    'lease_id'           => $invoice->lease_id,
                    'invoice_id'         => $invoice->id,
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type'               => 'adjustment',
                    'entry_type'         => 'debit',
                    'amount'             => $invoice->fresh()->total_amount,
                    'transaction_date'   => now()->toDateString(),
                    'description'        => 'Invoice ' . $invoice->invoice_number . ' edited by admin',
                    'notes'              => 'Fields changed: ' . implode(', ', array_keys($changeLog)),
                    'metadata'           => [
                        'updated_by' => auth()->id(),
                        'updated_at' => now()->toISOString(),
                        'changes'    => $changeLog,
                    ],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'invoice' => $invoice->fresh()->load('items.item'),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a payment for the invoice.
     */
    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card,debit_card,online,stripe,paypal,other',
            'reference_number' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $invoice = Invoice::with('lease.property')->findOrFail($id);
            $lease = $invoice->lease;
            $bedId = $lease->assignments()->where('is_current', true)->first()->bed_id ?? null;

            // Check for deposit invoice with same invoice number (for first invoice)
            $depositInvoice = null;
            $depositAmount = 0;

            if ($invoice->is_first_invoice && !$lease->deposit_collected) {
                $depositInvoice = Invoice::where('lease_id', $lease->id)
                    ->where('invoice_number', $invoice->invoice_number)
                    ->where('type', 'DEPOSIT')
                    ->where('status', '!=', 'PAID')
                    ->first();

                $depositAmount = $depositInvoice ? $depositInvoice->balance_due : 0;
            }

            // Total balance due (rent + deposit if applicable)
            $rentBalanceDue = $invoice->balance_due;
            $totalBalanceDue = $rentBalanceDue + $depositAmount;

            $paymentAmount = $request->amount;

            // Validate payment amount doesn't exceed total balance
            if ($paymentAmount > $totalBalanceDue) {
                return response()->json([
                    'success' => false,
                    'message' => "Payment amount cannot exceed the total balance due of $" . number_format($totalBalanceDue, 2)
                ], 422);
            }

            // Allocate payment: First to deposit (if applicable), then to rent
            $depositPaymentAmount = 0;
            $rentPaymentAmount = 0;

            if ($depositAmount > 0 && $paymentAmount > 0) {
                // Pay deposit first
                $depositPaymentAmount = min($paymentAmount, $depositAmount);
                $rentPaymentAmount = $paymentAmount - $depositPaymentAmount;
            } else {
                $rentPaymentAmount = $paymentAmount;
            }

            // Determine payment type
            $isFullPayment = ($paymentAmount >= $totalBalanceDue);
            $paymentType = $isFullPayment ? 'full' : 'partial';

            // Process Deposit Payment (if applicable)
            if ($depositPaymentAmount > 0 && $depositInvoice) {
                // Create Payment Record for Deposit
                $depositPayment = Payment::create([
                    'invoice_id' => $depositInvoice->id,
                    'tenant_id' => $invoice->tenant_id,
                    'lease_id' => $lease->id,
                    'bed_id' => $bedId,
                    'amount' => $depositPaymentAmount,
                    'payment_date' => Carbon::parse($request->payment_date)->format('Y-m-d'),
                    'payment_method' => $request->payment_method,
                    'reference_number' => $request->reference_number,
                    'payment_type' => $depositPaymentAmount >= $depositAmount ? 'full' : 'partial',
                    'paid_by' => 'admin',
                    'recorded_by' => auth()->id(),
                    'note' => $request->note ? $request->note . ' (Deposit portion)' : 'Deposit payment',
                    'metadata' => [
                        'recorded_at' => now()->toISOString(),
                        'ip_address' => $request->ip(),
                        'is_deposit_payment' => true,
                    ]
                ]);

                // Update Deposit Invoice
                $newDepositPaid = ($depositInvoice->paid_amount ?? 0) + $depositPaymentAmount;
                $newDepositBalance = $depositInvoice->total_amount - $newDepositPaid;

                $depositStatus = $newDepositBalance <= 0 ? 'PAID' : 'PARTIAL';

                $depositInvoice->update([
                    'paid_amount' => $newDepositPaid,
                    'balance_due' => max(0, $newDepositBalance),
                    'status' => $depositStatus,
                    'paid_at' => $depositStatus === 'PAID' ? now() : null,
                    'notes'=> $request->note ?? 'Deposit payment',
                ]);

                // Mark deposit as collected if fully paid
                if ($depositStatus === 'PAID') {
                    $lease->update(['deposit_collected' => 1]);
                }

                // Create Transaction for Deposit Payment
                Transaction::create([
                    'tenant_id' => $invoice->tenant_id,
                    'bed_id' => $bedId,
                    'lease_id' => $lease->id,
                    'invoice_id' => $depositInvoice->id,
                    'payment_id' => $depositPayment->id,
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type' => 'payment',
                    'entry_type' => 'credit',
                    'amount' => $depositPaymentAmount,
                    'transaction_date' => Carbon::parse($request->payment_date)->format('Y-m-d'),
                    'description' => 'Security deposit payment for Invoice ' . $invoice->invoice_number,
                    'notes' => $request->note,
                    'metadata' => [
                        'payment_method' => $request->payment_method,
                        'reference_number' => $request->reference_number,
                        'invoice_number' => $depositInvoice->invoice_number,
                        'is_deposit_payment' => true,
                    ]
                ]);
            }

            // Process Rent Payment (if any amount remaining for rent)
            $rentPayment = null;
            if ($rentPaymentAmount > 0) {
                // Create Payment Record for Rent
                $rentPayment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $invoice->tenant_id,
                    'lease_id' => $lease->id,
                    'bed_id' => $bedId,
                    'amount' => $rentPaymentAmount,
                    'payment_date' => Carbon::parse($request->payment_date)->format('Y-m-d'),
                    'payment_method' => $request->payment_method,
                    'reference_number' => $request->reference_number,
                    'payment_type' => ($rentPaymentAmount >= $rentBalanceDue) ? 'full' : 'partial',
                    'paid_by' => 'admin',
                    'recorded_by' => auth()->id(),
                    'note' => $request->note,
                    'metadata' => [
                        'recorded_at' => now()->toISOString(),
                        'ip_address' => $request->ip(),
                    ]
                ]);

                // Update Rent Invoice
                $newRentPaid = ($invoice->paid_amount ?? 0) + $rentPaymentAmount;
                $newRentBalance = $invoice->total_amount - $newRentPaid;

                $rentStatus = 'PARTIAL';
                $paidAt = null;

                if ($newRentBalance <= 0) {
                    $rentStatus = 'PAID';
                    $paidAt = now();
                    $newRentBalance = 0;
                }

                $invoice->update([
                    'paid_amount' => $newRentPaid,
                    'balance_due' => $newRentBalance,
                    'status' => $rentStatus,
                    'paid_at' => $paidAt,
                    'notes' => $request->note,
                ]);

                // Create Transaction for Rent Payment
                Transaction::create([
                    'tenant_id' => $invoice->tenant_id,
                    'bed_id' => $bedId,
                    'lease_id' => $lease->id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $rentPayment->id,
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type' => 'payment',
                    'entry_type' => 'credit',
                    'amount' => $rentPaymentAmount,
                    'transaction_date' => Carbon::parse($request->payment_date)->format('Y-m-d'),
                    'description' => $this->generatePaymentDescription($invoice, $rentPayment, $rentPaymentAmount >= $rentBalanceDue),
                    'notes' => $request->note,
                    'metadata' => [
                        'payment_method' => $request->payment_method,
                        'reference_number' => $request->reference_number,
                        'invoice_number' => $invoice->invoice_number,
                        'is_full_payment' => $rentPaymentAmount >= $rentBalanceDue,
                    ]
                ]);
            }

            DB::commit();

            // Build success message
            $message = '';
            if ($isFullPayment) {
                $message = 'Full payment of $' . number_format($paymentAmount, 2) . ' recorded successfully.';
                if ($depositPaymentAmount > 0) {
                    $message .= ' (Deposit: $' . number_format($depositPaymentAmount, 2) . ', Rent: $' . number_format($rentPaymentAmount, 2) . ')';
                }
            } else {
                $message = 'Partial payment of $' . number_format($paymentAmount, 2) . ' recorded successfully.';
                if ($depositPaymentAmount > 0 && $rentPaymentAmount > 0) {
                    $message .= ' (Deposit: $' . number_format($depositPaymentAmount, 2) . ', Rent: $' . number_format($rentPaymentAmount, 2) . ')';
                } elseif ($depositPaymentAmount > 0) {
                    $message .= ' (Applied to deposit)';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'payment' => $rentPayment ?? $depositPayment ?? null,
                'invoice' => $invoice->fresh(),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to store payment: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment. Please try again.' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history for an invoice.
     */
    public function getPayments($id)
    {
        try {
            $invoice = Invoice::with(['payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'payments' => $invoice->payments,
                'total_paid' => $invoice->paid_amount,
                'balance_due' => $invoice->balance_due,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments.'
            ], 500);
        }
    }

    /**
     * Cancel / void an invoice.
     * Works regardless of payment status — creates a full audit trail.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::with(['lease.assignments', 'payments'])->findOrFail($id);

            if ($invoice->status === 'CANCELLED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is already cancelled/voided.',
                ], 422);
            }

            $previousStatus = $invoice->status;
            $bedId = $invoice->lease->assignments()->where('is_current', true)->value('bed_id');
            $now   = now();

            // Mark invoice as CANCELLED with audit fields
            $invoice->update([
                'status'            => 'CANCELLED',
                'cancelled_reason'  => $request->reason,
                'cancelled_by'      => auth()->id(),
                'cancelled_at'      => $now,
            ]);

            // Audit transaction 1: void the outstanding balance
            Transaction::create([
                'tenant_id'          => $invoice->tenant_id,
                'bed_id'             => $bedId,
                'lease_id'           => $invoice->lease_id,
                'invoice_id'         => $invoice->id,
                'transaction_number' => $this->generateTransactionNumber(),
                'type'               => 'adjustment',
                'entry_type'         => 'credit',
                'amount'             => $invoice->balance_due,
                'transaction_date'   => $now->toDateString(),
                'description'        => 'Invoice ' . $invoice->invoice_number . ' voided — outstanding balance written off',
                'notes'              => $request->reason,
                'metadata'           => [
                    'voided_by'                   => auth()->id(),
                    'voided_at'                   => $now->toISOString(),
                    'previous_status'             => $previousStatus,
                    'paid_amount_at_cancellation' => $invoice->paid_amount,
                    'balance_at_cancellation'     => $invoice->balance_due,
                ],
            ]);

            // Audit transaction 2: note any already-collected payments
            if ((float) $invoice->paid_amount > 0) {
                Transaction::create([
                    'tenant_id'          => $invoice->tenant_id,
                    'bed_id'             => $bedId,
                    'lease_id'           => $invoice->lease_id,
                    'invoice_id'         => $invoice->id,
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type'               => 'adjustment',
                    'entry_type'         => 'debit',
                    'amount'             => $invoice->paid_amount,
                    'transaction_date'   => $now->toDateString(),
                    'description'        => 'Audit: Invoice ' . $invoice->invoice_number . ' voided with $'
                                            . number_format($invoice->paid_amount, 2) . ' in existing payments — manual refund required if applicable',
                    'notes'              => $request->reason,
                    'metadata'           => [
                        'voided_by'      => auth()->id(),
                        'voided_at'      => $now->toISOString(),
                        'payment_count'  => $invoice->payments->count(),
                        'total_paid'     => $invoice->paid_amount,
                        'requires_refund_review' => true,
                    ],
                ]);
            }

            DB::commit();

            $message = 'Invoice voided successfully.';
            if ((float) $invoice->paid_amount > 0) {
                $message .= ' Note: $' . number_format($invoice->paid_amount, 2)
                    . ' in payments were recorded on this invoice — please review refunds if applicable.';
            }

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'had_payments' => (float) $invoice->paid_amount > 0,
                'paid_amount'  => $invoice->paid_amount,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to void invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to void invoice.',
            ], 500);
        }
    }    /**
     * Cancel a mistaken paid invoice cash payment and revert the invoice to CANCELLED/VOIDED with ledger reversals.
     */
    public function cancelPaidCashPayment(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $currentUserId = Auth::id();
        if (!$this->isSuperAdmin($currentUserId)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Master Admin can cancel a paid cash invoice.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Pessimistic lock for update to prevent concurrent payment applications or voids
            $invoice = Invoice::lockForUpdate()->with(['lease.assignments', 'payments'])->findOrFail($id);

            if (!in_array($invoice->status, ['PAID', 'PARTIAL'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Only PAID or PARTIALLY PAID invoices can be voided with this action.',
                ], 422);
            }

            if ($invoice->payments->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No payment record found for this invoice.',
                ], 422);
            }

            $nonCashPayments = $invoice->payments->filter(function ($payment) {
                return strtolower($payment->payment_method) !== 'cash' && strtolower($payment->payment_method) !== 'check';
            });

            if ($nonCashPayments->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This action supports cash-only paid invoices.',
                ], 422);
            }

            $now = now();
            $bedId = $invoice->lease->assignments()->where('is_current', true)->value('bed_id');
            $activePayments = $invoice->payments->where('status', '!=', 'voided');

            if ($activePayments->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'All payments for this invoice have already been voided.',
                ], 422);
            }

            $removedAmount = (float) $activePayments->sum('amount');

            // 1. Mark Invoice as CANCELLED with audit fields
            $invoice->update([
                'status' => 'CANCELLED',
                'paid_amount' => 0.00,
                'balance_due' => 0.00, // Reversal neutralizes outstanding debt; balance becomes 0
                'cancelled_reason' => $request->reason,
                'cancelled_by' => $currentUserId,
                'cancelled_at' => $now,
            ]);

            // 2. Set active payments status to voided
            foreach ($activePayments as $payment) {
                $payment->update([
                    'status' => 'voided',
                    'void_reason' => $request->reason,
                    'voided_by' => $currentUserId,
                    'voided_at' => $now,
                ]);
            }

            // 3. Handle First Invoice Deposit Status Reversal
            if (
                $invoice->lease &&
                $invoice->is_first_invoice &&
                $invoice->includes_deposit &&
                $invoice->lease->deposit_collected
            ) {
                $invoice->lease->update(['deposit_collected' => false]);
            }

            // 4. Write Double-Entry Reversal General Ledger Transactions
            // Reversal Transaction A (Neutralize the original Invoice Debit charge)
            Transaction::create([
                'tenant_id' => $invoice->tenant_id,
                'bed_id' => $bedId,
                'lease_id' => $invoice->lease_id,
                'invoice_id' => $invoice->id,
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'adjustment',
                'entry_type' => 'credit',
                'amount' => $invoice->total_amount,
                'transaction_date' => $now->toDateString(),
                'description' => 'Invoice ' . $invoice->invoice_number . ' voided — original charge written off',
                'notes' => $request->reason,
                'metadata' => [
                    'action' => 'cancel_paid_cash_invoice',
                    'voided_by' => $currentUserId,
                    'voided_at' => $now->toISOString(),
                    'original_invoice_total' => $invoice->total_amount,
                ],
            ]);

            // Reversal Transaction B (Neutralize the active Payment Credits)
            foreach ($activePayments as $payment) {
                Transaction::create([
                    'tenant_id' => $invoice->tenant_id,
                    'bed_id' => $bedId,
                    'lease_id' => $invoice->lease_id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'transaction_number' => $this->generateTransactionNumber(),
                    'type' => 'adjustment',
                    'entry_type' => 'debit',
                    'amount' => $payment->amount,
                    'transaction_date' => $now->toDateString(),
                    'description' => 'Payment ' . $payment->payment_number . ' reversed due to invoice void',
                    'notes' => $request->reason,
                    'metadata' => [
                        'action' => 'cancel_paid_cash_invoice',
                        'voided_by' => $currentUserId,
                        'voided_at' => $now->toISOString(),
                        'reversed_payment_amount' => $payment->amount,
                        'payment_number' => $payment->payment_number,
                    ],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paid cash invoice has been voided successfully. Offset entries posted.',
                'invoice' => $invoice->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to void paid cash invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to void paid cash invoice: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Mark invoice as paid (quick action - full payment).
     */
    public function markPaid(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $invoice = Invoice::with('lease')->findOrFail($id);
            $lease = $invoice->lease;

            $balanceDue = $invoice->total_amount - ($invoice->paid_amount ?? 0);

            if ($balanceDue <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is already paid.'
                ], 422);
            }

            // Create payment for remaining balance
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'lease_id' => $lease->id,
                'bed_id' => $lease->bed_id,
                'amount' => $balanceDue,
                'payment_date' => now()->toDateString(),
                'payment_method' => $request->payment_method ?? 'other',
                'reference_number' => $request->reference_number,
                'payment_type' => 'full',
                'paid_by' => 'admin',
                'recorded_by' => auth()->id(),
                'note' => $request->note ?? 'Marked as paid by admin',
            ]);

            // Update invoice
            $invoice->update([
                'status' => 'PAID',
                'paid_amount' => $invoice->total_amount,
                'balance_due' => 0,
                'paid_at' => now()
            ]);

            // Mark deposit as collected if applicable
            if ($invoice->is_first_invoice && $invoice->includes_deposit && !$lease->deposit_collected) {
                $lease->update(['deposit_collected' => true]);
            }

            // Create transaction
            Transaction::create([
                'tenant_id' => $invoice->tenant_id,
                'bed_id' => $lease->bed_id,
                'lease_id' => $lease->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'payment',
                'entry_type' => 'credit',
                'amount' => $balanceDue,
                'transaction_date' => now()->toDateString(),
                'description' => 'Full payment for Invoice ' . $invoice->invoice_number,
                'metadata' => [
                    'marked_paid_by' => auth()->id(),
                    'is_full_payment' => true,
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark invoice as paid: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice status.'
            ], 500);
        }
    }

    /**
     * Generate a unique transaction number.
     */
    private function generateTransactionNumber(): string
    {
        $prefix = 'TXN-';
        $lastTransaction = Transaction::orderBy('id', 'desc')->first();
        $nextId = $lastTransaction ? $lastTransaction->id + 1 : 1;

        return $prefix . str_pad($nextId, 8, '0', STR_PAD_LEFT);
    }

    private function isSuperAdmin(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return User::whereKey($userId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'super admin');
            })
            ->exists();
    }

    /**
     * Generate payment description.
     */
    private function generatePaymentDescription(Invoice $invoice, Payment $payment, bool $isFullPayment): string
    {
        $paymentType = $isFullPayment ? 'Full payment' : 'Partial payment';
        $description = $paymentType . ' for Invoice ' . $invoice->invoice_number;

        if ($invoice->is_first_invoice && $invoice->includes_deposit && $isFullPayment) {
            $description .= ' (includes security deposit)';
        }

        return $description;
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadPdf($id)
    {
        $invoice = Invoice::with([
            'lease.property',
            'lease.tenant.profile',
            'lease.assignments.bed',
            'payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ])->findOrFail($id);

        $lease = $invoice->lease;

        // Determine if this is the first invoice
        $firstInvoice = $lease->invoices()
            ->where('type', 'RENT')
            ->orderBy('created_at', 'asc')
            ->first();

        $isFirstInvoice = $firstInvoice && $firstInvoice->id === $invoice->id;

        // Calculate totals
        $totalDue = $invoice->total_amount;

        if (!$totalDue || $totalDue == 0) {
            $totalDue = $invoice->amount;
        }

        $totalPaid = $invoice->paid_amount ?? $invoice->payments->sum('amount');
        $balanceDue = $invoice->balance_due ?? ($totalDue - $totalPaid);

        $data = compact(
            'invoice',
            'lease',
            'isFirstInvoice',
            'totalDue',
            'totalPaid',
            'balanceDue'
        );

        $pdf = Pdf::loadView('backend.layouts.leases.invoice.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Invoice-' . ($invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT)) . '.pdf';

        return $pdf->stream($filename);
    }
}
