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
                ])
                ->with([
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name'
                    ],
                    'invoice:id,invoice_number,total_amount,due_date',
                    'lease:id,property_id' => [
                        'property:id,name',
                        'assignments' => function ($q) {
                            $q->where('is_current', true)->with('bed:id,bed_label');
                        }
                    ],
                    'reviewedBy:id,name'
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
            if ($request->filled('date_from')) {
                $query->where('payments.payment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('payments.payment_date', '<=', $request->date_to);
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
                    return number_format($payment->amount, 2);
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
                    if ($payment->reviewed_at && $payment->reviewedBy) {
                        return $payment->reviewedBy->name . '<br><small class="text-muted">' .
                               $payment->reviewed_at->format('M d, Y H:i') . '</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('invoice_info', function ($payment) {
                    if ($payment->invoice) {
                        return $payment->invoice->invoice_number . '<br><small class="text-muted">Due: ' .
                               ($payment->invoice->due_date ? $payment->invoice->due_date->format('M d, Y') : 'N/A') . '</small>';
                    }
                    return 'N/A';
                })
                ->addColumn('actions', function ($payment) {
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
                ->rawColumns(['payment_method_badge', 'review_status_badge', 'reviewed_info', 'invoice_info', 'actions'])
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
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('review_status')) {
            $query->where('review_status', $request->review_status);
        }

        $summary = [
            'total_payments' => (clone $query)->count(),
            'total_amount' => (clone $query)->sum('amount'),
            'pending_review' => (clone $query)->where('review_status', 'pending')->orWhereNull('review_status')->count(),
            'pending_amount' => (clone $query)->where(function($q) {
                $q->where('review_status', 'pending')->orWhereNull('review_status');
            })->sum('amount'),
            'reviewed' => (clone $query)->where('review_status', 'reviewed')->count(),
            'reviewed_amount' => (clone $query)->where('review_status', 'reviewed')->sum('amount'),
            'confirmed' => (clone $query)->where('review_status', 'confirmed')->count(),
            'confirmed_amount' => (clone $query)->where('review_status', 'confirmed')->sum('amount'),
            'disputed' => (clone $query)->where('review_status', 'disputed')->count(),
            'disputed_amount' => (clone $query)->where('review_status', 'disputed')->sum('amount'),

            // By payment method
            'by_method' => (clone $query)
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
                'invoice:id,invoice_number,total_amount,due_date',
                'lease:id,property_id' => [
                    'property:id,name',
                    'assignments' => function ($q) {
                        $q->where('is_current', true)->with('bed:id,bed_label');
                    }
                ],
                'reviewedBy:id,name'
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
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
            $filters['date_from'] = date('M d, Y', strtotime($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
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
                'review_status' => ucfirst($payment->review_status ?? 'Pending'),
                'reviewed_by' => $payment->reviewedBy?->name ?? '-',
                'reviewed_at' => $payment->reviewed_at ? $payment->reviewed_at->format('M d, Y H:i') : '-',
                'note' => $payment->note ?? '',
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
}
