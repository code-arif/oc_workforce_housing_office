<?php

namespace App\Http\Controllers\Web\Backend\Reports;

use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Property;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RentReportExport;
use Yajra\DataTables\Facades\DataTables;

class RentReportController extends Controller
{
    /**
     * Display the rent report page
     */
    public function index()
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $tenants = Tenant::where('status', 'Approved')->orderBy('email')->get();
        return view('backend.layouts.reports.rent-report', compact('properties', 'tenants'));
    }

    /**
     * Get report data for DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Invoice::query()
                ->select([
                    'invoices.id',
                    'invoices.lease_id',
                    'invoices.tenant_id',
                    'invoices.invoice_number',
                    'invoices.total_amount',
                    'invoices.paid_amount',
                    'invoices.balance_due',
                    'invoices.due_date',
                    'invoices.status',
                    'invoices.notes',
                ])
                ->with([
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name'
                    ],
                    'lease:id,property_id' => [
                        'property:id,name',
                        'assignments' => function ($q) {
                            $q->where('is_current', true)->with('bed:id,bed_label');
                        }
                    ],
                ])
                ->whereIn('invoices.status', ['UNPAID', 'PARTIAL', 'OVERDUE']);

            // Property filter
            if ($request->filled('property_id')) {
                $query->whereHas('lease', function ($q) use ($request) {
                    $q->where('property_id', $request->property_id);
                });
            }

            // Tenant filter
            if ($request->filled('tenant_id')) {
                $query->where('invoices.tenant_id', $request->tenant_id);
            }

            // Bed filter
            if ($request->filled('bed_id')) {
                $query->whereHas('lease.assignments', function ($q) use ($request) {
                    $q->where('bed_id', $request->bed_id);
                });
            }

            // Date range filter (by due date)
            if ($request->filled('date_from')) {
                $query->where('invoices.due_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('invoices.due_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addColumn('property_name', function ($invoice) {
                    return $invoice->lease?->property?->name ?? 'N/A';
                })
                ->addColumn('bed_label', function ($invoice) {
                    $assignment = $invoice->lease?->assignments?->first();
                    return $assignment?->bed?->bed_label ?? 'N/A';
                })
                ->addColumn('tenant_name', function ($invoice) {
                    $profile = $invoice->tenant?->profile;
                    if ($profile) {
                        return trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    }
                    return 'N/A';
                })
                ->addColumn('due_date', function ($invoice) {
                    return $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A';
                })
                ->addColumn('outstanding_amount', function ($invoice) {
                    $outstanding = $invoice->total_amount - $invoice->paid_amount;
                    return number_format($outstanding, 2);
                })
                ->addColumn('notes', function ($invoice) {
                    return $invoice->notes ?? '';
                })
                ->addColumn('invoice_number', function ($invoice) {
                    return $invoice->invoice_number ?? 'N/A';
                })
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        
        $pdf = Pdf::loadView('backend.layouts.reports.rent-report-pdf', [
            'reportData' => $data['reportData'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
            'generatedAt' => now()->format('M d, Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'rent-report-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Export report to Excel using Maatwebsite Excel
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);
        
        $filename = 'rent-report-' . now()->format('Y-m-d-His') . '.xlsx';
        
        return Excel::download(
            new RentReportExport(
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
        $query = Invoice::query()
            ->with([
                'tenant:id,email' => [
                    'profile:id,tenant_id,first_name,middle_name,last_name'
                ],
                'lease:id,property_id' => [
                    'property:id,name',
                    'assignments' => function ($q) {
                        $q->where('is_current', true)->with('bed:id,bed_label');
                    }
                ],
            ])
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);

        $filters = [];

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

        // Bed filter
        if ($request->filled('bed_id')) {
            $query->whereHas('lease.assignments', function ($q) use ($request) {
                $q->where('bed_id', $request->bed_id);
            });
            $filters['bed'] = $request->bed_id;
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
            $filters['date_from'] = date('M d, Y', strtotime($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
            $filters['date_to'] = date('M d, Y', strtotime($request->date_to));
        }

        $invoices = $query->orderBy('due_date', 'asc')->get();

        $reportData = [];
        $totalOutstanding = 0;

        foreach ($invoices as $invoice) {
            $profile = $invoice->tenant?->profile;
            $tenantName = $profile 
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'N/A';

            $assignment = $invoice->lease?->assignments?->first();
            $bedLabel = $assignment?->bed?->bed_label ?? 'N/A';

            $outstanding = $invoice->total_amount - $invoice->paid_amount;
            $totalOutstanding += $outstanding;

            $reportData[] = [
                'property_name' => $invoice->lease?->property?->name ?? 'N/A',
                'bed_label' => $bedLabel,
                'tenant_name' => $tenantName,
                'due_date' => $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A',
                'outstanding_amount' => number_format($outstanding, 2),
                'notes' => $invoice->notes ?? '',
                'invoice_number' => $invoice->invoice_number ?? 'N/A',
            ];
        }

        return [
            'reportData' => $reportData,
            'summary' => [
                'total_invoices' => count($reportData),
                'total_outstanding' => $totalOutstanding,
            ],
            'filters' => $filters,
        ];
    }
}
