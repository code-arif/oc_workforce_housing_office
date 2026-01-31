<?php

namespace App\Http\Controllers\Web\Backend\Reports;

use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TenantReportExport;
use Yajra\DataTables\Facades\DataTables;

class TenantReportController extends Controller
{
    /**
     * Display the tenant report page
     */
    public function index()
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        return view('backend.layouts.reports.tenant-report', compact('properties'));
    }

    /**
     * Get report data for DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Tenant::query()
                ->select([
                    'tenants.id',
                    'tenants.email',
                    'tenants.status',
                    'tenants.move_in_date',
                    'tenants.application_source',
                    'tenants.created_at',
                ])
                ->with([
                    'profile:id,tenant_id,first_name,middle_name,last_name,phone',
                    'address:id,tenant_id,city,state',
                    'leases' => function ($q) {
                        $q->where('status', 'ACTIVE')
                            ->with([
                                'property:id,name',
                                'assignments' => function ($q) {
                                    $q->where('is_current', true)->with('bed:id,bed_label');
                                }
                            ]);
                    }
                ]);

            // Status filter
            if ($request->filled('status')) {
                $query->where('tenants.status', $request->status);
            }

            // Property filter (through active lease)
            if ($request->filled('property_id')) {
                $query->whereHas('leases', function ($q) use ($request) {
                    $q->where('property_id', $request->property_id)
                        ->where('status', 'ACTIVE');
                });
            }

            // Application source filter
            if ($request->filled('application_source')) {
                $query->where('tenants.application_source', $request->application_source);
            }

            // Date range filter (move-in date)
            if ($request->filled('date_from')) {
                $query->where('tenants.move_in_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('tenants.move_in_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addColumn('tenant_name', function ($tenant) {
                    $profile = $tenant->profile;
                    if ($profile) {
                        return trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    }
                    return 'N/A';
                })
                ->addColumn('phone', function ($tenant) {
                    return $tenant->profile?->phone ?? 'N/A';
                })
                ->addColumn('location', function ($tenant) {
                    $address = $tenant->address;
                    if ($address) {
                        return trim(($address->city ?? '') . ', ' . ($address->state ?? ''));
                    }
                    return 'N/A';
                })
                ->addColumn('property_name', function ($tenant) {
                    $activeLease = $tenant->leases->first();
                    return $activeLease?->property?->name ?? 'N/A';
                })
                ->addColumn('unit', function ($tenant) {
                    $activeLease = $tenant->leases->first();
                    $assignment = $activeLease?->assignments->first();
                    return $assignment?->bed?->bed_label ?? 'N/A';
                })
                ->addColumn('move_in_date', function ($tenant) {
                    return $tenant->move_in_date ? $tenant->move_in_date->format('M d, Y') : 'N/A';
                })
                ->addColumn('lease_status', function ($tenant) {
                    $activeLease = $tenant->leases->first();
                    return $activeLease ? $activeLease->status : 'No Active Lease';
                })
                ->addColumn('status_badge', function ($tenant) {
                    $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'under_review' => 'secondary',
                        'approved' => 'primary',
                        'active' => 'success',
                        'rejected' => 'danger',
                    ];
                    $color = $statusColors[strtolower($tenant->status)] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst(str_replace('_', ' ', $tenant->status)) . '</span>';
                })
                ->rawColumns(['status_badge'])
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

        $pdf = Pdf::loadView('backend.layouts.reports.tenant-report-pdf', [
            'reportData' => $data['reportData'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
            'generatedAt' => now()->format('M d, Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        $filename = 'tenant-report-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Export report to Excel using Maatwebsite Excel
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);

        $filename = 'tenant-report-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new TenantReportExport(
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
        $query = Tenant::query()
            ->with([
                'profile:id,tenant_id,first_name,middle_name,last_name,phone',
                'address:id,tenant_id,city,state',
                'leases' => function ($q) {
                    $q->where('status', 'ACTIVE')
                        ->with([
                            'property:id,name',
                            'assignments' => function ($q) {
                                $q->where('is_current', true)->with('bed:id,bed_label');
                            }
                        ]);
                }
            ]);

        $filters = [];

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
            $filters['status'] = ucfirst(str_replace('_', ' ', $request->status));
        }

        // Property filter
        if ($request->filled('property_id')) {
            $query->whereHas('leases', function ($q) use ($request) {
                $q->where('property_id', $request->property_id)
                    ->where('status', 'ACTIVE');
            });
            $property = Property::find($request->property_id);
            $filters['property'] = $property?->name ?? 'Selected Property';
        }

        // Application source filter
        if ($request->filled('application_source')) {
            $query->where('application_source', $request->application_source);
            $filters['application_source'] = ucfirst($request->application_source) . ' Applied';
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('move_in_date', '>=', $request->date_from);
            $filters['date_from'] = date('M d, Y', strtotime($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->where('move_in_date', '<=', $request->date_to);
            $filters['date_to'] = date('M d, Y', strtotime($request->date_to));
        }

        $tenants = $query->orderBy('created_at', 'desc')->get();

        $reportData = [];
        $activeTenants = 0;
        $approvedTenants = 0;
        $pendingTenants = 0;

        foreach ($tenants as $tenant) {
            $profile = $tenant->profile;
            $tenantName = $profile
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'N/A';

            $activeLease = $tenant->leases->first();
            $assignment = $activeLease?->assignments->first();
            $propertyName = $activeLease?->property?->name ?? 'N/A';
            $unit = $assignment?->bed?->bed_label ?? 'N/A';
            $leaseStatus = $activeLease ? $activeLease->status : 'No Active Lease';

            // Count by status
            $statusLower = strtolower($tenant->status);
            if ($statusLower === 'active') {
                $activeTenants++;
            } elseif ($statusLower === 'approved') {
                $approvedTenants++;
            } elseif (in_array($statusLower, ['pending', 'processing', 'under_review'])) {
                $pendingTenants++;
            }

            $reportData[] = [
                'tenant_name' => $tenantName,
                'email' => $tenant->email ?? 'N/A',
                'phone' => $profile?->phone ?? 'N/A',
                'status' => ucfirst(str_replace('_', ' ', $tenant->status)),
                'property_name' => $propertyName,
                'unit' => $unit,
                'move_in_date' => $tenant->move_in_date ? $tenant->move_in_date->format('M d, Y') : 'N/A',
                'lease_status' => $leaseStatus,
            ];
        }

        return [
            'reportData' => $reportData,
            'summary' => [
                'total_tenants' => count($reportData),
                'active_tenants' => $activeTenants,
                'approved_tenants' => $approvedTenants,
                'pending_tenants' => $pendingTenants,
            ],
            'filters' => $filters,
        ];
    }
}
