<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use Exception;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentManageController extends Controller
{
    /**
     * Get tenant payment history
     */
    public function getPaymentHistory($tenantId)
    {
        try {
            $payments = Payment::with([
                'invoice' => function ($q) {
                    $q->select('id', 'invoice_number', 'type', 'total_amount', 'lease_id');
                },
                'lease' => function ($q) {
                    $q->select('id', 'property_id')->with('property:id,name');
                }
            ])
                ->where('tenant_id', $tenantId)
                ->orderBy('payment_date', 'desc')
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_number' => $payment->payment_number,
                        'invoice_number' => $payment->invoice->invoice_number ?? 'N/A',
                        'invoice_type' => $payment->invoice->type ?? 'N/A',
                        'amount' => number_format($payment->amount, 2),
                        'payment_date' => $payment->payment_date,
                        'payment_method' => ucfirst($payment->payment_method),
                        'payment_type' => ucfirst($payment->payment_type),
                        'reference_number' => $payment->reference_number ?? 'N/A',
                        'gateway_transaction_id' => $payment->gateway_transaction_id ?? 'N/A',
                        'property_name' => $payment->lease->property->name ?? 'N/A',
                        'paid_by' => ucfirst($payment->paid_by),
                        'review_status' => $payment->review_status ?? 'pending',
                        'reviewed_at' => $payment->reviewed_at ? date('M d, Y H:i', strtotime($payment->reviewed_at)) : null,
                        'note' => $payment->note,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant transaction history
     */
    public function getTransactionHistory($tenantId)
    {
        try {
            $transactions = Transaction::with([
                'invoice:id,invoice_number,type',
                'payment:id,payment_number,payment_method',
                'lease' => function ($q) {
                    $q->select('id', 'property_id')->with('property:id,name');
                }
            ])
                ->where('tenant_id', $tenantId)
                ->orderBy('transaction_date', 'desc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'transaction_number' => $transaction->transaction_number,
                        'type' => ucfirst($transaction->type),
                        'entry_type' => ucfirst($transaction->entry_type),
                        'amount' => number_format($transaction->amount, 2),
                        'transaction_date' => $transaction->transaction_date,
                        'description' => $transaction->description,
                        'invoice_number' => $transaction->invoice->invoice_number ?? 'N/A',
                        'payment_number' => $transaction->payment->payment_number ?? 'N/A',
                        'payment_method' => $transaction->payment ? ucfirst($transaction->payment->payment_method) : 'N/A',
                        'property_name' => $transaction->lease->property->name ?? 'N/A',
                        'notes' => $transaction->notes,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details for review
     */
    public function getPaymentDetails($paymentId)
    {
        try {
            $payment = Payment::with([
                'invoice' => function ($q) {
                    $q->with('lease.property');
                },
                'tenant' => function ($q) {
                    $q->with('profile', 'address');
                },
                'lease' => function ($q) {
                    $q->with('property', 'assignments.bed');
                },
                'reviewedBy'
            ])->findOrFail($paymentId);

            $tenant = $payment->tenant;
            $profile = $tenant->profile;
            $invoice = $payment->invoice;
            $lease = $payment->lease;
            $assignment = $lease->assignments->where('is_current', true)->first();

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'reference_number' => $payment->reference_number,
                    'gateway_transaction_id' => $payment->gateway_transaction_id,
                    'paid_by' => $payment->paid_by,
                    'note' => $payment->note,
                    'review_status' => $payment->review_status ?? 'pending',
                    'reviewed_at' => $payment->reviewed_at,
                    'reviewed_by_name' => $payment->reviewedBy ? $payment->reviewedBy->name : null,
                    'review_note' => $payment->review_note,
                    'created_at' => $payment->created_at->format('M d, Y H:i A'),
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $profile ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')) : 'N/A',
                    'email' => $tenant->email,
                    'phone' => $profile->phone ?? 'N/A',
                    'address' => $tenant->address ? $tenant->address->address : 'N/A',
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date->format('M d, Y'),
                ],
                'lease' => [
                    'id' => $lease->id,
                    'property_name' => $lease->property->name ?? 'N/A',
                    'unit' => $assignment ? $assignment->bed->bed_label : 'N/A',
                    'rent_amount' => $lease->rent_amount,
                    'start_date' => $lease->start_date->format('M d, Y'),
                    'end_date' => $lease->end_date->format('M d, Y'),
                ],
                'metadata' => $payment->metadata ?? []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    /**
     * Update payment review status
     */
    public function updatePaymentReview(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'review_status' => 'required|in:pending,reviewed,confirmed,disputed',
            'review_note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payment = Payment::findOrFail($paymentId);

            $payment->update([
                'review_status' => $request->review_status,
                'review_note' => $request->review_note,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment review updated successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export payment history to CSV
     */
    public function exportPaymentHistory($tenantId)
    {
        try {
            $tenant = Tenant::with('profile')->findOrFail($tenantId);
            $profile = $tenant->profile;
            $tenantName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'Tenant';

            $payments = Payment::with([
                'invoice',
                'lease.property'
            ])
                ->where('tenant_id', $tenantId)
                ->orderBy('payment_date', 'desc')
                ->get();

            $fileName = 'payment_history_' . $tenantName . '_' . date('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function () use ($payments) {
                $file = fopen('php://output', 'w');

                // Header
                fputcsv($file, [
                    'Payment Number',
                    'Invoice Number',
                    'Amount',
                    'Payment Date',
                    'Payment Method',
                    'Payment Type',
                    'Reference Number',
                    'Gateway Transaction ID',
                    'Property',
                    'Status',
                    'Reviewed At',
                    'Note'
                ]);

                // Data
                foreach ($payments as $payment) {
                    fputcsv($file, [
                        $payment->payment_number,
                        $payment->invoice->invoice_number ?? 'N/A',
                        '$' . number_format($payment->amount, 2),
                        date('M d, Y', strtotime($payment->payment_date)),
                        ucfirst($payment->payment_method),
                        ucfirst($payment->payment_type),
                        $payment->reference_number ?? 'N/A',
                        $payment->gateway_transaction_id ?? 'N/A',
                        $payment->lease->property->name ?? 'N/A',
                        ucfirst($payment->review_status ?? 'pending'),
                        $payment->reviewed_at ? date('M d, Y H:i', strtotime($payment->reviewed_at)) : 'Not Reviewed',
                        $payment->note ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export payment history: ' . $e->getMessage()
            ], 500);
        }
    }
}
