<?php

namespace App\Http\Controllers\Web\Backend\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    /**
     * Display the transaction monitoring page.
     */
    public function index()
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $tenants = Tenant::where('status', 'Approved')
            ->with('profile:id,tenant_id,first_name,last_name')
            ->orderBy('email')
            ->get();

        return view('backend.layouts.transactions.index', compact('properties', 'tenants'));
    }

    /**
     * Get all payments/transactions data for DataTables.
     */
    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $query = Payment::query()
            ->select([
                'payments.id',
                'payments.invoice_id',
                'payments.tenant_id',
                'payments.lease_id',
                'payments.payment_number',
                'payments.amount',
                'payments.base_amount',
                'payments.processing_fee',
                'payments.total_charged',
                'payments.payment_date',
                'payments.deposit_date',
                'payments.payment_method',
                'payments.reference_number',
                'payments.gateway_transaction_id',
                'payments.stripe_payment_intent_id',
                'payments.payment_type',
                'payments.paid_by',
                'payments.note',
                'payments.review_status',
                'payments.reviewed_at',
                'payments.reviewed_by',
                'payments.review_note',
                'payments.status',
                'payments.void_reason',
                'payments.voided_by',
                'payments.voided_at',
                'payments.created_at',
                'payments.metadata',
            ])
            ->with([
                'tenant:id,email' => [
                    'profile:id,tenant_id,first_name,middle_name,last_name,phone'
                ],
                'invoice:id,invoice_number,total_amount,due_date,type,status,stripe_payment_method,stripe_exact_amount',
                'lease:id,property_id,rent_amount,start_date,end_date' => [
                    'property:id,name',
                    'assignments' => function ($q) {
                        $q->where('is_current', true)->with('bed:id,bed_label');
                    }
                ],
                'reviewedBy:id,name',
                'voidedBy:id,name',
                'recordedBy:id,name',
            ]);

        // Apply filters
        if ($request->filled('review_status')) {
            $query->where('payments.review_status', $request->review_status);
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'voided') {
                $query->where('payments.status', 'voided');
            } else {
                $query->where('payments.status', '!=', 'voided');
            }
        }

        if ($request->filled('property_id')) {
            $query->whereHas('lease', function ($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }

        if ($request->filled('tenant_id')) {
            $query->where('payments.tenant_id', $request->tenant_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payments.payment_method', $request->payment_method);
        }

        if ($request->filled('payment_type')) {
            $query->where('payments.payment_type', $request->payment_type);
        }

        if ($from = $this->normalizeDate($request->date_from)) {
            $query->whereDate('payments.payment_date', '>=', $from);
        }
        if ($to = $this->normalizeDate($request->date_to)) {
            $query->whereDate('payments.payment_date', '<=', $to);
        }

        return DataTables::of($query)
            ->filterColumn('invoice_number', function ($query, $keyword) {
                $query->whereHas('invoice', function ($q) use ($keyword) {
                    $q->where('invoice_number', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('tenant_name', function ($query, $keyword) {
                $query->whereHas('tenant.profile', function ($q) use ($keyword) {
                    $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('property_name', function ($query, $keyword) {
                $query->whereHas('lease.property', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('property_name', function ($payment) {
                $property = $payment->lease?->property;
                if (!$property) {
                    return 'N/A';
                }
                $escapedName = e($property->name);
                return '<a href="' . route('property.show', $property->id) . '" class="text-decoration-none fw-semibold" target="_blank" title="View property details">'
                    . $escapedName . ' <i class="fe fe-external-link" style="font-size:11px;opacity:0.5;"></i></a>';
            })
            ->addColumn('tenant_name', function ($payment) {
                $profile = $payment->tenant?->profile;
                $tenantId = $payment->tenant_id;
                $name = 'N/A';

                if ($profile) {
                    $name = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                } elseif ($payment->tenant) {
                    $name = $payment->tenant->email;
                }

                $escapedName = e($name);
                $escapedEmail = $payment->tenant ? e($payment->tenant->email) : '';

                return '<a href="' . route('tenants.show', $tenantId) . '" class="text-decoration-none fw-semibold" target="_blank" title="View tenant details">'
                    . $escapedName . ' <i class="fe fe-external-link" style="font-size:11px;opacity:0.5;"></i></a>'
                    . '<br><small class="text-muted">' . $escapedEmail . '</small>';
            })
            ->addColumn('tenant_phone', function ($payment) {
                return $payment->tenant?->profile?->phone ?? '-';
            })
            ->addColumn('tenant_email', function ($payment) {
                return $payment->tenant?->email ?? '-';
            })
            ->addColumn('formatted_payment_date', function ($payment) {
                return $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A';
            })
            ->addColumn('formatted_amount', function ($payment) {
                $amount = number_format($payment->amount, 2);
                if ($payment->status === 'voided') {
                    return '<span class="text-decoration-line-through text-danger fw-bold">$' . $amount . '</span>';
                }
                return '$' . $amount;
            })
            ->addColumn('payment_method_badge', function ($payment) {
                $badges = [
                    'cash' => 'bg-success',
                    'check' => 'bg-info',
                    'bank_transfer' => 'bg-warning text-dark',
                    'credit_card' => 'bg-primary',
                    'debit_card' => 'bg-secondary',
                    'online' => 'bg-purple',
                    'stripe' => 'bg-indigo',
                    'paypal' => 'bg-blue',
                    'other' => 'bg-secondary',
                ];
                $method = strtolower($payment->payment_method ?? 'other');
                $badge = $badges[$method] ?? 'bg-secondary';
                $label = ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A'));
                return "<span class='badge {$badge} p-2'>{$label}</span>";
            })
            ->addColumn('payment_type_badge', function ($payment) {
                $badges = [
                    'full' => 'bg-success',
                    'partial' => 'bg-warning text-dark',
                    'deposit' => 'bg-info',
                    'rent' => 'bg-primary',
                ];
                $type = strtolower($payment->payment_type ?? 'partial');
                $badge = $badges[$type] ?? 'bg-secondary';
                $label = ucfirst($payment->payment_type ?? 'N/A');
                return "<span class='badge {$badge} p-2'>{$label}</span>";
            })
            ->addColumn('invoice_info', function ($payment) {
                if ($payment->invoice) {
                    $status = $payment->invoice->status ?? 'N/A';
                    $statusBadge = match ($status) {
                        'PAID' => 'bg-success',
                        'PARTIAL' => 'bg-warning text-dark',
                        'UNPAID' => 'bg-secondary',
                        'OVERDUE' => 'bg-danger',
                        'CANCELLED' => 'bg-dark',
                        'PROCESSING' => 'bg-info',
                        default => 'bg-secondary',
                    };
                    $invoiceUrl = route('invoices.show', $payment->invoice_id);
                    $escapedInvoiceNumber = e($payment->invoice->invoice_number);
                    return '<div><a href="' . $invoiceUrl . '" class="text-decoration-none fw-semibold" target="_blank" title="View invoice details">'
                        . $escapedInvoiceNumber . ' <i class="fe fe-external-link" style="font-size:10px;opacity:0.4;"></i></a>' .
                        ' <span class="badge ' . $statusBadge . ' p-1" style="font-size:10px;">' . e($status) . '</span></div>' .
                        '<small class="text-muted">$' . number_format($payment->invoice->total_amount, 2) . '</small>';
                }
                return 'N/A';
            })
            ->addColumn('review_status_badge', function ($payment) {
                if ($payment->status === 'voided') {
                    $voidedBy = $payment->voidedBy?->name ?? 'System';
                    $voidReason = $payment->void_reason ? e($payment->void_reason) : 'No reason';
                    return '<span class="badge bg-danger px-2 py-1 d-inline-flex align-items-center" title="' . $voidReason . '">
                        <i class="fe fe-x-circle me-1" style="font-size:12px;"></i>VOIDED
                    </span><br><small class="text-muted">by ' . e($voidedBy) . '</small>';
                }
                $status = $payment->review_status ?? 'pending';
                $badges = [
                    'pending' => '<span class="badge bg-warning text-dark px-2 py-1 d-inline-flex align-items-center">
                        <i class="fe fe-clock me-1" style="font-size:12px;"></i>Pending</span>',
                    'reviewed' => '<span class="badge bg-info px-2 py-1 d-inline-flex align-items-center">
                        <i class="fe fe-eye me-1" style="font-size:12px;"></i>Reviewed</span>',
                    'confirmed' => '<span class="badge bg-success px-2 py-1 d-inline-flex align-items-center">
                        <i class="fe fe-check-circle me-1" style="font-size:12px;"></i>Confirmed</span>',
                    'disputed' => '<span class="badge bg-danger px-2 py-1 d-inline-flex align-items-center">
                        <i class="fe fe-alert-triangle me-1" style="font-size:12px;"></i>Disputed</span>',
                ];
                return $badges[$status] ?? $badges['pending'];
            })
            ->addColumn('paid_by_info', function ($payment) {
                $badgeColor = match ($payment->paid_by) {
                    'tenant' => 'bg-primary',
                    'admin' => 'bg-info',
                    'system' => 'bg-secondary',
                    default => 'bg-secondary',
                };
                $recordedBy = $payment->recordedBy?->name ?? '-';
                return '<span class="badge ' . $badgeColor . ' light p-2">' . ucfirst($payment->paid_by ?? 'N/A') . '</span>' .
                    '<br><small class="text-muted">' . e($recordedBy) . '</small>';
            })
            ->addColumn('payment_note', function ($payment) {
                $note = e($payment->note ?? '');
                $shortNote = $note ? e(Str::limit($payment->note, 40)) : '';
                $paymentId = $payment->id;
                $hasNote = !empty($payment->note);

                if ($payment->status === 'voided') {
                    return '<span class="text-muted fst-italic">Voided</span>';
                }

                return '<div class="inline-note-wrapper" data-payment-id="' . $paymentId . '">'
                    . '<div class="note-display d-flex align-items-center gap-1" style="cursor:pointer;">'
                    . '<span class="note-text text-truncate d-inline-block" style="max-width:140px;" title="' . ($note ?: 'Click to add note') . '">'
                    . ($hasNote ? $shortNote : '<span class="text-muted fst-italic">+ Add note</span>')
                    . '</span>'
                    . '<i class="fe fe-edit-2 text-muted" style="font-size:10px;opacity:0.6;flex-shrink:0;"></i>'
                    . '</div>'
                    . '<textarea class="form-control form-control-sm inline-note-editor d-none" rows="2"'
                    . ' style="min-width:160px;resize:vertical;" data-payment-id="' . $paymentId . '">' . $note . '</textarea>'
                    . '<div class="note-actions d-none mt-1 d-flex gap-1">'
                    . '<button type="button" class="btn btn-sm btn-success note-save-btn" data-payment-id="' . $paymentId . '">'
                    . '<i class="fe fe-check" style="font-size:11px;"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-light note-cancel-btn">'
                    . '<i class="fe fe-x" style="font-size:11px;"></i></button>'
                    . '</div>'
                    . '</div>';
            })
            ->addColumn('gateway_info', function ($payment) {
                $info = '';
                if ($payment->gateway_transaction_id) {
                    $info .= '<div><small class="text-muted">Gateway:</small><br><code style="font-size:10px;">' . e($payment->gateway_transaction_id) . '</code></div>';
                }
                if ($payment->stripe_payment_intent_id) {
                    $info .= '<div class="mt-1"><small class="text-muted">Intent:</small><br><code style="font-size:10px;">' . e($payment->stripe_payment_intent_id) . '</code></div>';
                }
                return $info ?: '<span class="text-muted">-</span>';
            })
            ->addColumn('actions', function ($payment) {
                $isVoided = $payment->status === 'voided';
                $buttons = '<div class="btn-group btn-group-sm">';

                // View details
                $buttons .= '<button type="button" class="btn btn-outline-secondary view-payment" data-id="' . $payment->id . '" title="View Details">
                    <i class="fe fe-eye"></i></button>';

                if (!$isVoided) {
                    // Review actions
                    if ($payment->review_status !== 'confirmed') {
                        if ($payment->review_status !== 'reviewed') {
                            $buttons .= '<button type="button" class="btn btn-outline-info review-payment" data-id="' . $payment->id . '" data-status="reviewed" title="Mark as Reviewed">
                                <i class="fe fe-check-square"></i></button>';
                        }
                        $buttons .= '<button type="button" class="btn btn-outline-success review-payment" data-id="' . $payment->id . '" data-status="confirmed" title="Confirm Payment">
                            <i class="fe fe-check"></i></button>';
                    } else {
                        $buttons .= '<button type="button" class="btn btn-outline-warning review-payment" data-id="' . $payment->id . '" data-status="pending" title="Reset to Pending">
                            <i class="fe fe-rotate-ccw"></i></button>';
                    }

                    // Void button
                    $buttons .= '<button type="button" class="btn btn-outline-danger void-payment" data-id="' . $payment->id . '" title="Void/Cancel Payment">
                        <i class="fe fe-x-circle"></i></button>';
                }

                $buttons .= '</div>';

                if ($isVoided) {
                    $buttons .= '<div class="mt-1"><small class="text-danger">' . e(Str::limit($payment->void_reason ?? 'Voided', 30)) . '</small></div>';
                }

                return $buttons;
            })
            ->rawColumns([
                'formatted_amount',
                'payment_method_badge',
                'payment_type_badge',
                'review_status_badge',
                'paid_by_info',
                'gateway_info',
                'invoice_info',
                'actions',
                'tenant_name',
                'property_name',
                'payment_note',
            ])
            ->make(true);
    }

    /**
     * Get payment details for the detail modal.
     */
    public function getPaymentDetails($id)
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
                'reviewedBy',
                'voidedBy',
                'recordedBy',
            ])->findOrFail($id);

            $tenant = $payment->tenant;
            $profile = $tenant->profile;
            $invoice = $payment->invoice;
            $lease = $payment->lease;
            $assignment = $lease?->assignments?->where('is_current', true)->first();

            // Get related transactions
            $transactions = Transaction::where('payment_id', $payment->id)
                ->orWhere(function ($q) use ($payment) {
                    $q->where('invoice_id', $payment->invoice_id)
                        ->where('type', '!=', 'payment');
                })
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($txn) {
                    return [
                        'transaction_number' => $txn->transaction_number,
                        'type' => $txn->type,
                        'entry_type' => $txn->entry_type,
                        'amount' => '$' . number_format($txn->amount, 2),
                        'date' => $txn->transaction_date?->format('M d, Y'),
                        'description' => $txn->description,
                    ];
                });

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => '$' . number_format($payment->amount, 2),
                    'base_amount' => $payment->base_amount ? '$' . number_format($payment->base_amount, 2) : '-',
                    'processing_fee' => $payment->processing_fee ? '$' . number_format($payment->processing_fee, 2) : '$0.00',
                    'total_charged' => $payment->total_charged ? '$' . number_format($payment->total_charged, 2) : '-',
                    'payment_date' => $payment->payment_date?->format('M d, Y'),
                    'deposit_date' => $payment->deposit_date?->format('M d, Y') ?? '-',
                    'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')),
                    'payment_type' => ucfirst($payment->payment_type ?? 'N/A'),
                    'reference_number' => $payment->reference_number ?? '-',
                    'gateway_transaction_id' => $payment->gateway_transaction_id ?? '-',
                    'stripe_payment_intent_id' => $payment->stripe_payment_intent_id ?? '-',
                    'paid_by' => ucfirst($payment->paid_by ?? 'N/A'),
                    'recorded_by_name' => $payment->recordedBy?->name ?? '-',
                    'note' => $payment->note ?? '-',
                    'status' => $payment->status ?? 'active',
                    'review_status' => $payment->review_status ?? 'pending',
                    'reviewed_at' => $payment->reviewed_at?->format('M d, Y H:i A') ?? '-',
                    'reviewed_by_name' => $payment->reviewedBy?->name ?? '-',
                    'review_note' => $payment->review_note ?? '-',
                    'void_reason' => $payment->void_reason ?? '-',
                    'voided_by_name' => $payment->voidedBy?->name ?? '-',
                    'voided_at' => $payment->voided_at?->format('M d, Y H:i A') ?? '-',
                    'created_at' => $payment->created_at?->format('M d, Y H:i A'),
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $profile ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')) : 'N/A',
                    'email' => $tenant->email,
                    'phone' => $profile->phone ?? 'N/A',
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                    'total_amount' => '$' . number_format($invoice->total_amount, 2),
                    'paid_amount' => '$' . number_format($invoice->paid_amount ?? 0, 2),
                    'balance_due' => '$' . number_format($invoice->balance_due ?? 0, 2),
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date?->format('M d, Y'),
                ],
                'lease' => [
                    'id' => $lease->id,
                    'property_name' => $lease->property->name ?? 'N/A',
                    'bed_label' => $assignment?->bed?->bed_label ?? 'N/A',
                    'rent_amount' => '$' . number_format($lease->rent_amount, 2),
                    'start_date' => $lease->start_date?->format('M d, Y'),
                    'end_date' => $lease->end_date?->format('M d, Y'),
                ],
                'transactions' => $transactions,
                'metadata' => $payment->metadata ?? [],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Void/cancel a payment.
     */
    public function voidPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment = Payment::with([
                'invoice',
                'lease.assignments',
                'tenant',
            ])->findOrFail($id);

            if ($payment->status === 'voided') {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment has already been voided.',
                ], 422);
            }

            $now = now();
            $userId = Auth::id();
            $bedId = $payment->lease?->assignments()
                ->where('is_current', true)
                ->value('bed_id') ?? $payment->bed_id;

            // Void the payment
            $payment->update([
                'status' => 'voided',
                'void_reason' => $request->reason,
                'voided_by' => $userId,
                'voided_at' => $now,
            ]);

            // Create void reversal transaction
            Transaction::create([
                'tenant_id' => $payment->tenant_id,
                'bed_id' => $bedId,
                'lease_id' => $payment->lease_id,
                'invoice_id' => $payment->invoice_id,
                'payment_id' => $payment->id,
                'transaction_number' => 'VOID-' . strtoupper(uniqid()),
                'type' => 'refund',
                'entry_type' => 'debit',
                'amount' => $payment->amount,
                'transaction_date' => $now->toDateString(),
                'description' => 'Payment ' . $payment->payment_number . ' voided — ' . $request->reason,
                'notes' => $request->reason,
                'metadata' => [
                    'voided_by' => $userId,
                    'voided_at' => $now->toISOString(),
                    'original_payment_number' => $payment->payment_number,
                    'original_payment_method' => $payment->payment_method,
                    'action' => 'payment_void',
                ],
            ]);

            // Update invoice payment status
            if ($payment->invoice) {
                $payment->invoice->updatePaymentStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment #' . $payment->payment_number . ' has been voided successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to void payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to void payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update payment review status.
     */
    public function updateReviewStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,confirmed,disputed',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = Payment::findOrFail($id);

            if ($payment->status === 'voided') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update review status for a voided payment.',
                ], 422);
            }

            $payment->review_status = $request->status;
            $payment->review_note = $request->note;

            if (in_array($request->status, ['reviewed', 'confirmed', 'disputed'])) {
                $payment->reviewed_at = now();
                $payment->reviewed_by = Auth::id();
            } else {
                $payment->reviewed_at = null;
                $payment->reviewed_by = null;
            }

            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated to "' . ucfirst($request->status) . '" successfully.',
                'status' => $payment->review_status,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update payment note (inline editing).
     */
    public function updateNote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = Payment::findOrFail($id);

            if ($payment->status === 'voided') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit note for a voided payment.',
                ], 422);
            }

            $payment->update([
                'note' => $request->note,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment note updated successfully.',
                'note' => e($request->note ?: ''),
                'preview' => e(Str::limit($request->note, 40) ?: ''),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update note: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get summary statistics for the dashboard.
     */
    public function getSummary(Request $request)
    {
        $query = Payment::query();

        // Apply filters
        if ($request->filled('property_id')) {
            $query->whereHas('lease', function ($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($from = $this->normalizeDate($request->date_from)) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to = $this->normalizeDate($request->date_to)) {
            $query->whereDate('payment_date', '<=', $to);
        }
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->review_status);
        }

        // Active payments (non-voided) for financial stats
        $activeQuery = (clone $query)->where('payments.status', '!=', 'voided');
        // Voided payments for void stats
        $voidedQuery = (clone $query)->where('payments.status', 'voided');
        // All payments for total count
        $allQuery = clone $query;

        $summary = [
            // Active payment stats
            'total_active_payments' => $activeQuery->count(),
            'total_active_amount' => (float) $activeQuery->sum('amount'),
            'total_processing_fees' => (float) $activeQuery->sum('processing_fee'),

            // Voided stats
            'total_voided' => $voidedQuery->count(),
            'total_voided_amount' => (float) $voidedQuery->sum('amount'),

            // Total payments (all)
            'total_all_payments' => $allQuery->count(),
            'total_all_amount' => (float) $allQuery->sum('amount'),

            // Review status breakdown (active only)
            'pending_review' => (clone $activeQuery)
                ->where(function ($q) {
                    $q->where('review_status', 'pending')->orWhereNull('review_status');
                })->count(),
            'pending_amount' => (float) (clone $activeQuery)
                ->where(function ($q) {
                    $q->where('review_status', 'pending')->orWhereNull('review_status');
                })->sum('amount'),
            'confirmed' => (clone $activeQuery)->where('review_status', 'confirmed')->count(),
            'confirmed_amount' => (float) (clone $activeQuery)->where('review_status', 'confirmed')->sum('amount'),
            'reviewed' => (clone $activeQuery)->where('review_status', 'reviewed')->count(),
            'reviewed_amount' => (float) (clone $activeQuery)->where('review_status', 'reviewed')->sum('amount'),
            'disputed' => (clone $activeQuery)->where('review_status', 'disputed')->count(),
            'disputed_amount' => (float) (clone $activeQuery)->where('review_status', 'disputed')->sum('amount'),

            // Payment method breakdown (active only)
            'by_method' => (clone $activeQuery)
                ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get()
                ->keyBy('payment_method')
                ->toArray(),

            // Payment type breakdown (active only)
            'by_type' => (clone $activeQuery)
                ->select('payment_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('payment_type')
                ->get()
                ->keyBy('payment_type')
                ->toArray(),
        ];

        return response()->json($summary);
    }

    /**
     * Export transactions to Excel.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new TransactionExport($data['rows'], $data['summary']),
            $filename
        );
    }

    /**
     * Export transactions to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        $pdf = Pdf::loadView('backend.layouts.transactions.pdf', [
            'reportData' => $data['rows'],
            'summary' => $data['summary'],
            'generatedAt' => now()->format('M d, Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Export transactions to CSV.
     */
    public function exportCsv(Request $request)
    {
        $data = $this->getExportData($request);

        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.csv';

        return Excel::download(
            new TransactionExport($data['rows'], $data['summary']),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    /**
     * Get export data with filters applied.
     */
    private function getExportData(Request $request): array
    {
        $query = Payment::query()
            ->with([
                'tenant:id,email' => [
                    'profile:id,tenant_id,first_name,middle_name,last_name'
                ],
                'invoice:id,invoice_number',
                'lease:id,property_id' => [
                    'property:id,name',
                ],
                'reviewedBy:id,name',
                'voidedBy:id,name',
            ]);

        // Apply same filters as getData
        if ($request->filled('review_status')) {
            $query->where('payments.review_status', $request->review_status);
        }
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'voided') {
                $query->where('payments.status', 'voided');
            } else {
                $query->where('payments.status', '!=', 'voided');
            }
        }
        if ($request->filled('property_id')) {
            $query->whereHas('lease', function ($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }
        if ($request->filled('tenant_id')) {
            $query->where('payments.tenant_id', $request->tenant_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('payments.payment_method', $request->payment_method);
        }
        if ($request->filled('payment_type')) {
            $query->where('payments.payment_type', $request->payment_type);
        }
        if ($from = $this->normalizeDate($request->date_from)) {
            $query->whereDate('payments.payment_date', '>=', $from);
        }
        if ($to = $this->normalizeDate($request->date_to)) {
            $query->whereDate('payments.payment_date', '<=', $to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $rows = [];
        foreach ($payments as $payment) {
            $profile = $payment->tenant?->profile;
            $tenantName = $profile
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : ($payment->tenant?->email ?? 'N/A');

            $isVoided = $payment->status === 'voided';
            $reviewStatusLabel = $isVoided ? 'Voided' : ucfirst($payment->review_status ?? 'Pending');

            $rows[] = [
                'payment_number' => $payment->payment_number ?? 'N/A',
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A',
                'tenant_name' => $tenantName,
                'tenant_email' => $payment->tenant?->email ?? 'N/A',
                'property_name' => $payment->lease?->property?->name ?? 'N/A',
                'amount' => '$' . number_format($payment->amount, 2) . ($isVoided ? ' (VOIDED)' : ''),
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')),
                'payment_type' => ucfirst($payment->payment_type ?? 'N/A'),
                'invoice_number' => $payment->invoice?->invoice_number ?? 'N/A',
                'paid_by' => ucfirst($payment->paid_by ?? 'N/A'),
                'reference_number' => $payment->reference_number ?? '-',
                'gateway_transaction_id' => $payment->gateway_transaction_id ?? '-',
                'processing_fee' => '$' . number_format($payment->processing_fee ?? 0, 2),
                'total_charged' => $payment->total_charged ? '$' . number_format($payment->total_charged, 2) : '-',
                'review_status' => $reviewStatusLabel,
                'status' => $payment->status ?? 'active',
                'note' => $payment->note ?? '',
            ];
        }

        // Build summary (same as getSummary logic)
        $activeQuery = clone $query;
        $activeQuery->where('payments.status', '!=', 'voided');
        $voidedQuery = clone $query;
        $voidedQuery->where('payments.status', 'voided');

        $summary = [
            'total_active_payments' => $activeQuery->count(),
            'total_active_amount' => (float) $activeQuery->sum('amount'),
            'total_processing_fees' => (float) $activeQuery->sum('processing_fee'),
            'total_voided' => $voidedQuery->count(),
            'total_voided_amount' => (float) $voidedQuery->sum('amount'),
            'pending_review' => (clone $activeQuery)
                ->where(function ($q) { $q->where('review_status', 'pending')->orWhereNull('review_status'); })->count(),
            'pending_amount' => (float) (clone $activeQuery)
                ->where(function ($q) { $q->where('review_status', 'pending')->orWhereNull('review_status'); })->sum('amount'),
            'confirmed' => (clone $activeQuery)->where('review_status', 'confirmed')->count(),
            'confirmed_amount' => (float) (clone $activeQuery)->where('review_status', 'confirmed')->sum('amount'),
            'disputed' => (clone $activeQuery)->where('review_status', 'disputed')->count(),
            'disputed_amount' => (float) (clone $activeQuery)->where('review_status', 'disputed')->sum('amount'),
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    /**
     * Get chart data for the dashboard chart view.
     */
    public function getChartData(Request $request)
    {
        try {
            $monthsBack = (int) $request->get('months', 12);

            $query = Payment::query()
                ->select(
                    DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                    DB::raw("SUM(CASE WHEN status != 'voided' THEN amount ELSE 0 END) as collected"),
                    DB::raw("SUM(CASE WHEN status != 'voided' THEN COALESCE(processing_fee, 0) ELSE 0 END) as fees"),
                    DB::raw("SUM(CASE WHEN status = 'voided' THEN amount ELSE 0 END) as voided"),
                    DB::raw("COUNT(*) as total_payments"),
                    DB::raw("SUM(CASE WHEN status = 'voided' THEN 1 ELSE 0 END) as voided_count"),
                )
                ->where('payment_date', '>=', now()->subMonths($monthsBack)->startOfMonth())
                ->groupBy(DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"))
                ->orderBy('month')
                ->get();

            $labels = [];
            $collected = [];
            $fees = [];
            $voided = [];

            foreach ($query as $row) {
                $labels[] = \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('M Y');
                $collected[] = (float) $row->collected;
                $fees[] = (float) $row->fees;
                $voided[] = (float) $row->voided;
            }

            // Payment method breakdown (active payments)
            $methodBreakdown = Payment::query()
                ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->where('payment_date', '>=', now()->subMonths($monthsBack)->startOfMonth())
                ->where('status', '!=', 'voided')
                ->groupBy('payment_method')
                ->get()
                ->map(function ($row) {
                    return [
                        'label' => ucfirst(str_replace('_', ' ', $row->payment_method ?? 'N/A')),
                        'total' => (float) $row->total,
                        'count' => (int) $row->count,
                    ];
                });

            // Review status breakdown
            $reviewBreakdown = Payment::query()
                ->select('review_status', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->where('payment_date', '>=', now()->subMonths($monthsBack)->startOfMonth())
                ->where('status', '!=', 'voided')
                ->groupBy('review_status')
                ->get()
                ->map(function ($row) {
                    $label = $row->review_status ? ucfirst($row->review_status) : 'Pending';
                    return [
                        'label' => $label,
                        'total' => (float) $row->total,
                        'count' => (int) $row->count,
                    ];
                });

            return response()->json([
                'success' => true,
                'labels' => $labels,
                'datasets' => [
                    'collected' => $collected,
                    'fees' => $fees,
                    'voided' => $voided,
                ],
                'method_breakdown' => $methodBreakdown,
                'review_breakdown' => $reviewBreakdown,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Normalize date string to Y-m-d format.
     */
    private function normalizeDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        $date = trim($date);

        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
                return Carbon::createFromFormat('m/d/Y', $date)->format('Y-m-d');
            }
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
}
