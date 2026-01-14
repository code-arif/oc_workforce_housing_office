<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            $canMakePayment = !$previousInvoice || $previousInvoice->status === 'PAID';
        }

        // Calculate totals - use stored values if available
        $totalDue = $invoice->total_amount;
        
        // If the total_amount is not set, calculate it
        if (!$totalDue || $totalDue == 0) {
            $totalDue = $invoice->amount;
            if ($isFirstInvoice && !$lease->deposit_collected && $lease->deposit_amount > 0) {
                $totalDue += $lease->deposit_amount;
            }
        }

        $totalPaid = $invoice->paid_amount ?? $invoice->payments->sum('amount');
        $balanceDue = $invoice->balance_due ?? ($totalDue - $totalPaid);
        // dd($invoice, $totalDue, $totalPaid, $balanceDue);
        return view('backend.layouts.leases.invoice.show', compact(
            'invoice', 
            'lease', 
            'isFirstInvoice',
            'canMakePayment',
            'totalDue',
            'totalPaid',
            'balanceDue'
        ));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:UNPAID,PARTIAL,PAID,OVERDUE,CANCELLED',
        ]);

        try {
            $invoice = Invoice::findOrFail($id);
            
            $updateData = [];
            
            if ($request->has('amount')) {
                $updateData['amount'] = $request->amount;
                // Recalculate total if amount changes
                $lease = $invoice->lease;
                $isFirstInvoice = $invoice->is_first_invoice;
                
                $totalAmount = $request->amount;
                if ($isFirstInvoice && $invoice->includes_deposit && !$lease->deposit_collected) {
                    $totalAmount += $lease->deposit_amount;
                }
                $updateData['total_amount'] = $totalAmount;
                $updateData['balance_due'] = $totalAmount - $invoice->paid_amount;
            }
            
            if ($request->has('due_date')) {
                $updateData['due_date'] = $request->due_date;
            }
            
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }
            
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                if ($request->status === 'PAID') {
                    $updateData['paid_at'] = now();
                }
            }

            $invoice->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'invoice' => $invoice->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update invoice: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice.'
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
                    'payment_date' => $request->payment_date,
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
                ]);

                // Mark deposit as collected if fully paid
                if ($depositStatus === 'PAID') {
                    $lease->update(['deposit_collected' => true]);
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
                    'transaction_date' => $request->payment_date,
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
                    'payment_date' => $request->payment_date,
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
                    'transaction_date' => $request->payment_date,
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

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store payment: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment. Please try again.'
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments.'
            ], 500);
        }
    }

    /**
     * Cancel an invoice.
     */
    public function cancel($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Don't allow cancellation if payments have been made
            if ($invoice->paid_amount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel an invoice that has payments. Please refund payments first.'
                ], 422);
            }

            $invoice->update([
                'status' => 'CANCELLED',
            ]);

            // Create transaction record for cancellation
            Transaction::create([
                'tenant_id' => $invoice->tenant_id,
                'bed_id' => $invoice->lease->bed_id,
                'lease_id' => $invoice->lease_id,
                'invoice_id' => $invoice->id,
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'adjustment',
                'entry_type' => 'credit', // Removes the debt
                'amount' => $invoice->total_amount,
                'transaction_date' => now(),
                'description' => 'Invoice ' . $invoice->invoice_number . ' cancelled',
                'metadata' => [
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now()->toISOString(),
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel invoice: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invoice.'
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
}
