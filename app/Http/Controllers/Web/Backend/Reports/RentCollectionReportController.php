<?php

namespace App\Http\Controllers\Web\Backend\Reports;

use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RentCollectionReportExport;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RentCollectionReportController extends Controller
{
    /**
     * Display the rent collection report page
     */
    public function index()
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $tenants = Tenant::where('status', 'Approved')
            ->with('profile:id,tenant_id,first_name,last_name')
            ->orderBy('email')
            ->get();

        return view('backend.layouts.reports.rent-collection-report', compact('properties', 'tenants'));
    }

    /**
     * Get report data for DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Payment::query()
                ->select([
                    'payments.id',
                    'payments.invoice_id',
                    'payments.tenant_id',
                    'payments.lease_id',
                    'payments.payment_number',
                    'payments.amount',
                    'payments.payment_date',
                    'payments.payment_method',
                    'payments.reference_number',
                    'payments.payment_type',
                    'payments.note',
                    'payments.review_status',
                    'payments.reviewed_at',
                    'payments.reviewed_by',
                    'payments.created_at',
                    'payments.status',
                    'payments.void_reason',
                    'payments.voided_by',
                    'payments.voided_at',
                    'payments.base_amount',
                    'payments.processing_fee',
                    'payments.total_charged',
                    'payments.metadata',
                ])
                ->with([
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name'
                    ],
                    'invoice:id,invoice_number,total_amount,due_date,stripe_payment_method,stripe_exact_amount',
                    'lease:id,property_id' => [
                        'property:id,name',
                        'assignments' => function ($q) {
                            $q->where('is_current', true)->with('bed:id,bed_label');
                        }
                    ],
                    'reviewedBy:id,name',
                    'voidedBy:id,name'
                ]);

            // Review status filter
            if ($request->filled('review_status')) {
                $query->where('payments.review_status', $request->review_status);
            }

            // Property filter
            if ($request->filled('property_id')) {
                $query->whereHas('lease', function ($q) use ($request) {
                    $q->where('property_id', $request->property_id);
                });
            }

            // Tenant filter
            if ($request->filled('tenant_id')) {
                $query->where('payments.tenant_id', $request->tenant_id);
            }

            // Payment method filter
            if ($request->filled('payment_method')) {
                $query->where('payments.payment_method', $request->payment_method);
            }

            // Date range filter (by payment date)
            if ($from = $this->normalizeRequestDate($request->date_from)) {
                $query->whereDate('payments.payment_date', '>=', $from);
            }
            if ($to = $this->normalizeRequestDate($request->date_to)) {
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
                ->filterColumn('reviewed_by', function ($query, $keyword) {
                    $query->whereHas('reviewedBy', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('bed_label', function ($query, $keyword) {
                    $query->whereHas('lease.assignments.bed', function ($q) use ($keyword) {
                        $q->where('bed_label', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('property_name', function ($payment) {
                    return $payment->lease?->property?->name ?? 'N/A';
                })
                ->addColumn('bed_label', function ($payment) {
                    $assignment = $payment->lease?->assignments?->first();
                    return $assignment?->bed?->bed_label ?? 'N/A';
                })
                ->addColumn('tenant_name', function ($payment) {
                    $profile = $payment->tenant?->profile;
                    if ($profile) {
                        return trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    }
                    return 'N/A';
                })
                ->addColumn('formatted_payment_date', function ($payment) {
                    return $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A';
                })
                ->addColumn('formatted_amount', function ($payment) {
                    $amount = number_format($payment->amount, 2);
                    if ($payment->status === 'voided') {
                        return '<span class="text-decoration-line-through text-danger fw-bold">$' . $amount . '</span><br><small class="text-danger fw-bold">VOID</small>';
                    }
                    return '$' . $amount;
                })
                ->addColumn('payment_method_badge', function ($payment) {
                    $badges = [
                        'cash' => 'bg-success',
                        'check' => 'bg-info',
                        'credit_card' => 'bg-primary',
                        'bank_transfer' => 'bg-warning',
                        'stripe' => 'bg-purple',
                        'other' => 'bg-secondary',
                    ];
                    $method = strtolower($payment->payment_method ?? 'other');
                    $badge = $badges[$method] ?? 'bg-secondary';
                    $label = ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A'));
                    return "<span class='badge {$badge} p-3'>{$label}</span>";
                })
                ->addColumn('review_status_badge', function ($payment) {
                    if ($payment->status === 'voided') {
                        return '<span class="badge bg-danger px-2 py-1 d-inline-flex align-items-center"><i class="fe fe-x-circle me-1" style="font-size: 12px;"></i>VOID</span>';
                    }
                    $status = $payment->review_status ?? 'pending';
                    $badges = [
                        'pending' => '<span class="badge bg-warning text-dark px-2 py-1 d-inline-flex align-items-center"><i class="fe fe-clock me-1" style="font-size: 12px;"></i>Pending Review</span>',
                        'reviewed' => '<span class="badge bg-info px-2 py-1 d-inline-flex align-items-center"><i class="fe fe-eye me-1" style="font-size: 12px;"></i>Reviewed</span>',
                        'confirmed' => '<span class="badge bg-success px-2 py-1 d-inline-flex align-items-center"><i class="fe fe-check-circle me-1" style="font-size: 12px;"></i>Confirmed</span>',
                        'disputed' => '<span class="badge bg-danger px-2 py-1 d-inline-flex align-items-center"><i class="fe fe-alert-triangle me-1" style="font-size: 12px;"></i>Disputed</span>',
                    ];
                    return $badges[$status] ?? $badges['pending'];
                })
                ->addColumn('reviewed_info', function ($payment) {
                    if ($payment->status === 'voided') {
                        if ($payment->voided_at && $payment->voidedBy) {
                            return e($payment->voidedBy->name) . '<br><small class="text-danger fw-bold">Voided:<br>' .
                                   $payment->voided_at->format('M d, Y H:i') . '</small>';
                        }
                        return '<span class="text-danger fw-bold">Voided</span>';
                    }
                    if ($payment->reviewed_at && $payment->reviewedBy) {
                        return $payment->reviewedBy->name . '<br><small class="text-muted">' .
                               $payment->reviewed_at->format('M d, Y H:i') . '</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('stripe_method', function ($payment) {
                    // Read payment method type from payment metadata first, fall back to invoice
                    $paymentMethodType = $payment->metadata['stripe_payment_method_type'] ?? null;
                    if (!$paymentMethodType) {
                        $invoice = $payment->invoice;
                        $paymentMethodType = $invoice?->stripe_payment_method ?? null;
                    }
                    if (!$paymentMethodType) {
                        return '<span class="text-muted">-</span>';
                    }
                    if ($paymentMethodType === 'us_bank_account') {
                        $methodName = 'ACH';
                        $colorClass = 'bg-info';
                    } elseif ($paymentMethodType === 'card') {
                        $methodName = 'Card';
                        $colorClass = 'bg-primary';
                    } else {
                        // For any other value, show as-is
                        $methodName = ucfirst(str_replace('_', ' ', $paymentMethodType));
                        $colorClass = 'bg-secondary';
                    }
                    return '<span class="badge p-3 ' . $colorClass . '">' . $methodName . '</span>';
                })
                ->addColumn('stripe_amount', function ($payment) {
                    // Show exact amount charged on Stripe: total_charged > base_amount+fee > amount
                    $amount = $this->getStripeTotalCharged($payment);
                    if (!$amount || $amount <= 0) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<div class="fw-semibold text-success">$' . number_format((float)$amount, 2) . '</div>';
                })
                ->addColumn('raw_stripe_amount', function ($payment) {
                    return $this->getStripeTotalCharged($payment);
                })
                ->addColumn('invoice_info', function ($payment) {
                    if ($payment->invoice) {
                        return $payment->invoice->invoice_number . '<br><small class="text-muted">Due: ' .
                               ($payment->invoice->due_date ? $payment->invoice->due_date->format('M d, Y') : 'N/A') . '</small>';
                    }
                    return 'N/A';
                })
                ->addColumn('actions', function ($payment) {
                    if ($payment->status === 'voided') {
                        return '<div class="text-danger" style="max-width: 150px; white-space: normal;"><small class="fw-bold">Void Reason:</small><br><small>' . e($payment->void_reason) . '</small></div>';
                    }
                    $buttons = '<div class="btn-group btn-group-sm">';

                    if ($payment->review_status !== 'confirmed') {
                        if ($payment->review_status !== 'reviewed') {
                            $buttons .= '<button type="button" class="btn btn-outline-info btn-review" data-id="' . $payment->id . '" data-status="reviewed" title="Mark as Reviewed"><i class="fe fe-eye"></i></button>';
                        }
                        $buttons .= '<button type="button" class="btn btn-outline-success btn-review" data-id="' . $payment->id . '" data-status="confirmed" title="Confirm Payment"><i class="fe fe-check"></i></button>';
                        $buttons .= '<button type="button" class="btn btn-outline-danger btn-review" data-id="' . $payment->id . '" data-status="disputed" title="Mark as Disputed"><i class="fe fe-alert-triangle"></i></button>';
                    } else {
                        $buttons .= '<button type="button" class="btn btn-outline-warning btn-review" data-id="' . $payment->id . '" data-status="pending" title="Reset to Pending"><i class="fe fe-rotate-ccw"></i></button>';
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['formatted_amount', 'payment_method_badge', 'review_status_badge', 'reviewed_info', 'invoice_info', 'actions', 'stripe_method', 'stripe_amount'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Update payment review status
     */
    public function updateReviewStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,confirmed,disputed',
            'note' => 'nullable|string|max:500',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->payment_method === 'cash') {
            $payment->review_status = 'confirmed';
            $payment->reviewed_at = now();
            $payment->reviewed_by = $payment->reviewed_by ?? $payment->recorded_by ?? Auth::id();

            if ($request->filled('note')) {
                $payment->review_note = $request->note;
            }

            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Cash payment is auto-confirmed by admin.',
                'status' => $payment->review_status,
            ]);
        }

        $payment->review_status = $request->status;

        if (in_array($request->status, ['reviewed', 'confirmed', 'disputed'])) {
            $payment->reviewed_at = now();
            $payment->reviewed_by = Auth::id();
        } else {
            $payment->reviewed_at = null;
            $payment->reviewed_by = null;
        }

        if ($request->filled('note')) {
            $payment->review_note = $request->note;
        }

        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'status' => $payment->review_status,
        ]);
    }

    /**
     * Bulk update payment review status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'exists:payments,id',
            'status' => 'required|in:pending,reviewed,confirmed,disputed',
        ]);

        $cashPaymentIds = Payment::whereIn('id', $request->payment_ids)
            ->where('payment_method', 'cash')
            ->pluck('id')
            ->all();

        $nonCashPaymentIds = array_values(array_diff($request->payment_ids, $cashPaymentIds));

        if (!empty($cashPaymentIds)) {
            Payment::whereIn('id', $cashPaymentIds)->update([
                'review_status' => 'confirmed',
                'reviewed_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);
        }

        if (empty($nonCashPaymentIds)) {
            return response()->json([
                'success' => true,
                'message' => count($cashPaymentIds) . ' cash payment(s) kept as auto-confirmed by admin.',
            ]);
        }

        $updateData = ['review_status' => $request->status];

        if (in_array($request->status, ['reviewed', 'confirmed', 'disputed'])) {
            $updateData['reviewed_at'] = now();
            $updateData['reviewed_by'] = Auth::id();
        } else {
            $updateData['reviewed_at'] = null;
            $updateData['reviewed_by'] = null;
        }

        Payment::whereIn('id', $nonCashPaymentIds)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => count($nonCashPaymentIds) . ' payment(s) updated successfully' . (!empty($cashPaymentIds) ? ' (' . count($cashPaymentIds) . ' cash payment(s) remained confirmed).' : ''),
        ]);
    }

    /**
     * Get summary statistics
     */
    public function getSummary(Request $request)
    {
        $query = Payment::query();

        // Apply same filters as getData
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
        if ($from = $this->normalizeRequestDate($request->date_from)) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to = $this->normalizeRequestDate($request->date_to)) {
            $query->whereDate('payment_date', '<=', $to);
        }
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->review_status);
        }

        // Clone and filter out voided payments for all financial statistics
        $activeQuery = (clone $query)->where('payments.status', '!=', 'voided');

        $stripeTotalAmount = (clone $activeQuery)
            ->get()
            ->sum(fn ($payment) => $this->getStripeTotalCharged($payment));

        $summary = [
            'total_payments' => $activeQuery->count(),
            'total_amount' => $activeQuery->sum('amount'),
            'total_stripe_amount' => $stripeTotalAmount,
            'pending_review' => (clone $activeQuery)->where(function($q) {
                $q->where('review_status', 'pending')->orWhereNull('review_status');
            })->count(),
            'pending_amount' => (clone $activeQuery)->where(function($q) {
                $q->where('review_status', 'pending')->orWhereNull('review_status');
            })->sum('amount'),
            'reviewed' => (clone $activeQuery)->where('review_status', 'reviewed')->count(),
            'reviewed_amount' => (clone $activeQuery)->where('review_status', 'reviewed')->sum('amount'),
            'confirmed' => (clone $activeQuery)->where('review_status', 'confirmed')->count(),
            'confirmed_amount' => (clone $activeQuery)->where('review_status', 'confirmed')->sum('amount'),
            'disputed' => (clone $activeQuery)->where('review_status', 'disputed')->count(),
            'disputed_amount' => (clone $activeQuery)->where('review_status', 'disputed')->sum('amount'),

            // By payment method
            'by_method' => (clone $activeQuery)
                ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get()
                ->keyBy('payment_method')
                ->toArray(),
        ];

        return response()->json($summary);
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);

        $pdf = Pdf::loadView('backend.layouts.reports.rent-collection-report-pdf', [
            'reportData' => $data['reportData'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
            'generatedAt' => now()->format('M d, Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        $filename = 'rent-collection-report-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);

        $filename = 'rent-collection-report-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new RentCollectionReportExport(
                $data['reportData'],
                $data['summary'],
                $data['filters']
            ),
            $filename
        );
    }

    /**
     * Get report data for exports
     */
    private function getReportData(Request $request): array
    {
        $query = Payment::query()
            ->with([
                'tenant:id,email' => [
                    'profile:id,tenant_id,first_name,middle_name,last_name'
                ],
                'invoice:id,invoice_number,total_amount,due_date,stripe_payment_method,stripe_exact_amount',
                'lease:id,property_id' => [
                    'property:id,name',
                    'assignments' => function ($q) {
                        $q->where('is_current', true)->with('bed:id,bed_label');
                    }
                ],
                'reviewedBy:id,name',
                'voidedBy:id,name'
            ])
            ->select([
                'payments.*'
            ]);

        $filters = [];

        // Review status filter
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->review_status);
            $filters['review_status'] = ucfirst($request->review_status);
        }

        // Property filter
        if ($request->filled('property_id')) {
            $query->whereHas('lease', function ($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
            $property = Property::find($request->property_id);
            $filters['property'] = $property?->name ?? 'Selected Property';
        }

        // Tenant filter
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
            $tenant = Tenant::with('profile')->find($request->tenant_id);
            $filters['tenant'] = $tenant?->profile ?
                trim($tenant->profile->first_name . ' ' . $tenant->profile->last_name) :
                'Selected Tenant';
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
            $filters['payment_method'] = ucfirst(str_replace('_', ' ', $request->payment_method));
        }

        // Date range filter
        if ($from = $this->normalizeRequestDate($request->date_from)) {
            $query->whereDate('payment_date', '>=', $from);
            $filters['date_from'] = date('M d, Y', strtotime($request->date_from));
        }
        if ($to = $this->normalizeRequestDate($request->date_to)) {
            $query->whereDate('payment_date', '<=', $to);
            $filters['date_to'] = date('M d, Y', strtotime($request->date_to));
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $reportData = [];
        $totalCollected = 0;
        $confirmedTotal = 0;
        $pendingTotal = 0;
        $disputedTotal = 0;

        foreach ($payments as $payment) {
            $profile = $payment->tenant?->profile;
            $tenantName = $profile
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'N/A';

            $assignment = $payment->lease?->assignments?->first();
            $bedLabel = $assignment?->bed?->bed_label ?? 'N/A';

            $isVoided = $payment->status === 'voided';

            if (!$isVoided) {
                $totalCollected += $payment->amount;

                switch ($payment->review_status) {
                    case 'confirmed':
                        $confirmedTotal += $payment->amount;
                        break;
                    case 'disputed':
                        $disputedTotal += $payment->amount;
                        break;
                    default:
                        $pendingTotal += $payment->amount;
                }
            }

            $reportData[] = [
                'payment_number' => $payment->payment_number ?? 'N/A',
                'property_name' => $payment->lease?->property?->name ?? 'N/A',
                'bed_label' => $bedLabel,
                'tenant_name' => $tenantName,
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A',
                'amount' => number_format($payment->amount, 2),
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')),
                'reference_number' => $payment->reference_number ?? '-',
                'invoice_number' => $payment->invoice?->invoice_number ?? 'N/A',
                'review_status' => $isVoided ? 'VOID' : ucfirst($payment->review_status ?? 'Pending'),
                'reviewed_by' => $isVoided ? ($payment->voidedBy?->name ?? '-') : ($payment->reviewedBy?->name ?? '-'),
                'reviewed_at' => $isVoided ? ($payment->voided_at ? $payment->voided_at->format('M d, Y H:i') : '-') : ($payment->reviewed_at ? $payment->reviewed_at->format('M d, Y H:i') : '-'),
                'note' => $isVoided ? 'VOIDED: ' . ($payment->void_reason ?? '') : ($payment->note ?? ''),
                'stripe_method' => $this->resolveStripeMethod($payment),
                'stripe_amount' => number_format((float)($this->getStripeTotalCharged($payment) ?: 0), 2),
                'status' => $payment->status,
            ];
        }

        return [
            'reportData' => $reportData,
            'summary' => [
                'total_payments' => count($reportData),
                'total_collected' => $totalCollected,
                'confirmed_total' => $confirmedTotal,
                'pending_total' => $pendingTotal,
                'disputed_total' => $disputedTotal,
            ],
            'filters' => $filters,
        ];
    }

    /**
     * Get the exact total amount charged on Stripe for a payment.
     * Priority: total_charged > base_amount + processing_fee > amount
     *
     * Note: For old pre-V2 records where total_charged/base_amount/processing_fee
     * were not stored, this falls back to payment->amount (the base amount).
     * The invoice's stripe_exact_amount is a cumulative sum across ALL payments
     * on an invoice, so it cannot be reliably attributed to individual payments.
     */
    private function getStripeTotalCharged($payment): float
    {
        // 1. Use total_charged if available (new V2 records — base + processing fee)
        if ($payment->total_charged !== null && $payment->total_charged > 0) {
            return (float) $payment->total_charged;
        }

        // 2. Calculate from base_amount + processing_fee (records with fee columns but no total_charged)
        if ($payment->base_amount !== null && $payment->processing_fee !== null) {
            $calculated = (float) $payment->base_amount + (float) $payment->processing_fee;
            if ($calculated > 0) {
                return round($calculated, 2);
            }
        }

        // 3. Fallback to amount (base amount without fees — for old pre-V2 records)
        return (float) ($payment->amount ?? 0);
    }

    /**
     * Resolve the stripe payment method label for display/export.
     */
    private function resolveStripeMethod($payment): string
    {
        $type = $payment->metadata['stripe_payment_method_type'] ?? $payment->invoice?->stripe_payment_method;
        if (!$type) {
            return 'N/A';
        }
        if ($type === 'us_bank_account') {
            return 'ACH';
        }
        if ($type === 'card') {
            return 'Card';
        }
        return ucfirst(str_replace('_', ' ', $type));
    }

    private function normalizeRequestDate($date)
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
        } catch (\Exception $e) {
            return null;
        }
    }
}
