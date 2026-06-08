<?php

namespace App\Http\Controllers\Web\Backend\Reports;

use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PropertyReportExport;
use App\Models\Season;
use Yajra\DataTables\Facades\DataTables;

class PropertyReportController extends Controller
{
    /**
     * Display the property report page
     */
    public function index()
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $tenants = Tenant::where('status', 'Approved')->orderBy('email')->get();
        $seasons = Season::where('is_active', true)->orderBy('name')->get();
        return view('backend.layouts.reports.property-report', compact('properties', 'tenants', 'seasons'));
    }

    /**
     * Get report data for DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Lease::query()
                ->select([
                    'leases.id',
                    'leases.tenant_id',
                    'leases.property_id',
                    'leases.status',
                    'leases.start_date',
                    'leases.end_date',
                    'leases.rent_amount',
                ])
                ->with([
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name'
                    ],
                    'property:id,name',
                    'assignments' => function ($q) {
                        $q->where('is_current', true)->with('bed:id,bed_label');
                    },
                    'invoices' => function ($q) {
                        $q->select('id', 'lease_id', 'total_amount', 'paid_amount', 'status', 'type');
                    }
                ])
                ->whereIn('leases.status', ['ACTIVE', 'COMPLETED', 'TERMINATED']);

            // Property filter
            if ($request->filled('property_id')) {
                $query->where('leases.property_id', $request->property_id);
            }

            if($request->filled('tenant_id')) {
                $query->where('leases.tenant_id', $request->tenant_id);
            }

            if($request->filled('bed_id')) {
                $query->whereHas('assignments', function ($q) use ($request) {
                    $q->where('bed_id', $request->bed_id);
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('leases.status', $request->status);
            }

            if($request->filled('season')) {
                $seasonId = $request->season;
                $query->whereHas('property', function ($q) use ($seasonId) {
                    $q->where('season_id', $seasonId);
                });
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->where('leases.start_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('leases.end_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('property_name', function ($lease) {
                    return $lease->property?->name ?? 'N/A';
                })
                ->addColumn('bed_label', function ($lease) {
                    $assignment = $lease->assignments->first();
                    return $assignment?->bed?->bed_label ?? 'N/A';
                })
                ->addColumn('tenant_name', function ($lease) {
                    $profile = $lease->tenant?->profile;
                    if ($profile) {
                        return trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    }
                    return 'N/A';
                })
                ->addColumn('security_deposit', function ($lease) {
                    $securityDeposit = $lease->invoices->where('type', 'DEPOSIT')->sum('total_amount');
                    return number_format($securityDeposit, 2);
                })
                ->addColumn('total_due', function ($lease) {
                    $totalDue = $lease->invoices->where('type', '!=', 'DEPOSIT')->sum('total_amount');
                    return number_format($totalDue, 2);
                })
                ->addColumn('total_paid', function ($lease) {
                    $totalPaid = $lease->invoices->where('type', '!=', 'DEPOSIT')->sum('paid_amount');
                    return number_format($totalPaid, 2);
                })
                ->addColumn('balance_owed', function ($lease) {
                    $totalDue = $lease->invoices->where('type', '!=', 'DEPOSIT')->sum('total_amount');
                    $totalPaid = $lease->invoices->where('type', '!=', 'DEPOSIT')->sum('paid_amount');
                    $balance = $totalDue - $totalPaid;
                    return number_format($balance, 2);
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
        
        $pdf = Pdf::loadView('backend.layouts.reports.property-report-pdf', [
            'reportData' => $data['reportData'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
            'generatedAt' => now()->format('M d, Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'property-report-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Export report to Excel using Maatwebsite Excel
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);
        
        $filename = 'property-report-' . now()->format('Y-m-d-His') . '.xlsx';
        
        return Excel::download(
            new PropertyReportExport(
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
        $query = Lease::query()
            ->with([
                'tenant:id,email' => [
                    'profile:id,tenant_id,first_name,middle_name,last_name'
                ],
                'property:id,name',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed:id,bed_label');
                },
                'invoices' => function ($q) {
                    $q->select('id', 'lease_id', 'total_amount', 'paid_amount', 'status', 'type');
                }
            ])
            ->whereIn('status', ['ACTIVE', 'COMPLETED', 'TERMINATED']);

        $filters = [];

        // Property filter
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
            $property = Property::find($request->property_id);
            $filters['property'] = $property?->name ?? 'Selected Property';
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
            $filters['status'] = ucwords(str_replace('_', ' ', strtolower($request->status)));
        }

        if($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
            $filters['tenant'] = $request->tenant_id;
        }

        if($request->filled('bed_id')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('bed_id', $request->bed_id);
            });
            $filters['bed'] = $request->bed_id;
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
            $filters['date_from'] = date('M d, Y', strtotime($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
            $filters['date_to'] = date('M d, Y', strtotime($request->date_to));
        }

        $leases = $query->orderBy('property_id')->get();

        $reportData = [];
        $totalDue = 0;
        $totalPaid = 0;
        $totalSecurityDeposit = 0;

        foreach ($leases as $lease) {
            $profile = $lease->tenant?->profile;
            $tenantName = $profile 
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'N/A';

            $assignment = $lease->assignments->first();
            $bedLabel = $assignment?->bed?->bed_label ?? 'N/A';

            $nonDepositInvoices = $lease->invoices->where('type', '!=', 'DEPOSIT');
            $depositInvoices = $lease->invoices->where('type', 'DEPOSIT');

            $leaseTotalDue = $nonDepositInvoices->sum('total_amount');
            $leaseTotalPaid = $nonDepositInvoices->sum('paid_amount');
            $leaseBalance = $leaseTotalDue - $leaseTotalPaid;
            $leaseSecurityDeposit = $depositInvoices->sum('total_amount');

            $totalDue += $leaseTotalDue;
            $totalPaid += $leaseTotalPaid;
            $totalSecurityDeposit += $leaseSecurityDeposit;

            $reportData[] = [
                'property_name' => $lease->property?->name ?? 'N/A',
                'bed_label' => $bedLabel,
                'tenant_name' => $tenantName,
                'security_deposit' => number_format($leaseSecurityDeposit, 2),
                'total_due' => number_format($leaseTotalDue, 2),
                'total_paid' => number_format($leaseTotalPaid, 2),
                'balance_owed' => number_format($leaseBalance, 2),
            ];
        }

        return [
            'reportData' => $reportData,
            'summary' => [
                'total_leases' => count($reportData),
                'total_security_deposit' => $totalSecurityDeposit,
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'total_balance' => $totalDue - $totalPaid,
            ],
            'filters' => $filters,
        ];
    }
}
