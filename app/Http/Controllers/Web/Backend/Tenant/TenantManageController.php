<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class TenantManageController extends Controller
{
    /**
     * All tenant list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Optimized query with eager loading and selective columns
            $query = Tenant::query()
                ->select([
                    'tenants.id',
                    'tenants.email',
                    'tenants.status',
                    'tenants.application_source',
                    'tenants.move_in_date',
                    'tenants.created_at'
                ])
                ->with([
                    'profile:id,tenant_id,first_name,middle_name,last_name,phone,avatar',
                    'leases' => function ($query) {
                        $query->select('id', 'tenant_id', 'status', 'start_date', 'end_date', 'rent_amount')
                            ->whereNull('deleted_at')
                            ->with([
                                'property:id,name',
                                'assignments' => function ($q) {
                                    $q->select('id', 'lease_id', 'bed_id', 'is_current')
                                        ->where('is_current', true)
                                        ->whereNull('deleted_at')
                                        ->with('bed:id,bed_number,room_id')
                                        ->limit(1);
                                }
                            ]);
                    }
                ])
                ->orderBy('tenants.id', 'desc');

            // Status filter
            if ($request->filled('status')) {
                $query->where('tenants.status', $request->status);
            }

            // Application source filter
            if ($request->filled('source')) {
                $query->where('tenants.application_source', $request->source);
            }

            // Account status filter (active leases)
            if ($request->filled('account_status')) {
                if ($request->account_status === 'active') {
                    $query->whereHas('leases', function ($q) {
                        $q->where('status', 'ACTIVE')
                            ->whereNull('deleted_at');
                    });
                } elseif ($request->account_status === 'inactive') {
                    $query->whereDoesntHave('leases', function ($q) {
                        $q->where('status', 'ACTIVE')
                            ->whereNull('deleted_at');
                    });
                }
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('tenants.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tenants.created_at', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('name', function ($query, $keyword) {
                    $query->whereHas('profile', function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('tenants.email', 'like', "%{$keyword}%");
                })
                ->addColumn('name', function ($data) {
                    $profile = $data->profile;
                    if (!$profile) {
                        return '<div class="d-flex align-items-center">
                                    <div class="avatar avatar-md rounded-circle bg-secondary">
                                        <span class="text-white">N/A</span>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-semibold">No Profile</div>
                                        <small class="text-muted">Not Available</small>
                                    </div>
                                </div>';
                    }

                    $fullName = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    $avatar = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                <div>
                                    <div class="fw-semibold">' . e($fullName) . '</div>
                                    <small class="text-muted">' . e($profile->phone ?? 'No phone') . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('property_unit', function ($data) {
                    $activeLease = $data->leases->where('status', 'ACTIVE')->first();

                    if (!$activeLease) {
                        return '<span class="text-muted">No Active Lease</span>';
                    }

                    $property = $activeLease->property;
                    $assignment = $activeLease->assignments->first();

                    $propertyName = $property ? e($property->name) : 'N/A';
                    $unitInfo = $assignment && $assignment->bed
                        ? 'Bed ' . e($assignment->bed->bed_number)
                        : 'N/A';

                    return '<div>
                                <div class="fw-semibold">' . $propertyName . '</div>
                                <small class="text-muted">' . $unitInfo . '</small>
                            </div>';
                })
                ->addColumn('address', function ($data) {
                    $activeLease = $data->leases->where('status', 'ACTIVE')->first();

                    if (!$activeLease || !$activeLease->property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    // Assuming property has address fields
                    return '<small class="text-muted">Property Address</small>';
                })
                ->addColumn('account_status', function ($data) {
                    $hasActiveLease = $data->leases->where('status', 'ACTIVE')->isNotEmpty();

                    if ($hasActiveLease) {
                        return '<span class="badge bg-success p-3">Active</span>';
                    }

                    return '<span class="badge bg-secondary p-3">Inactive</span>';
                })
                ->addColumn('tenant_status', function ($data) {
                    $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'under_review' => 'primary',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'active' => 'success',
                        'inactive' => 'secondary'
                    ];

                    $color = $statusColors[$data->status] ?? 'secondary';
                    return '<span class="badge p-3 bg-' . $color . '">' . e(ucfirst($data->status)) . '</span>';
                })
                ->addColumn('rent', function ($data) {
                    $activeLease = $data->leases->where('status', 'ACTIVE')->first();

                    if (!$activeLease) {
                        return '<span class="text-muted">$0.00</span>';
                    }

                    return '<span class="fw-semibold">$' . number_format($activeLease->rent_amount, 2) . '</span>';
                })
                ->addColumn('roommates', function ($data) {
                    // Count roommates based on active lease assignments
                    $activeLease = $data->leases->where('status', 'ACTIVE')->first();

                    if (!$activeLease) {
                        return '<span class="text-muted">0</span>';
                    }

                    $assignment = $activeLease->assignments->first();
                    if (!$assignment || !$assignment->bed) {
                        return '<span class="text-muted">0</span>';
                    }

                    // Get room_id and count other tenants in same room
                    $roomId = $assignment->bed->room_id;
                    $roommatesCount = DB::table('lease_assignments')
                        ->join('leases', 'lease_assignments.lease_id', '=', 'leases.id')
                        ->join('beds', 'lease_assignments.bed_id', '=', 'beds.id')
                        ->where('beds.room_id', $roomId)
                        ->where('lease_assignments.is_current', true)
                        ->where('leases.status', 'ACTIVE')
                        ->where('leases.tenant_id', '!=', $data->id)
                        ->whereNull('lease_assignments.deleted_at')
                        ->whereNull('leases.deleted_at')
                        ->count();

                    return '<span>' . $roommatesCount . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('tenants.show', $data->id) . '" class="btn btn-primary" title="View Details">
                                    <i class="fe fe-eye"></i>
                                </a>
                                <button type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger" title="Delete Tenant">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['name', 'property_unit', 'address', 'account_status', 'tenant_status', 'rent', 'action'])
                ->make(true);
        }

        // Get statistics for cards
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereHas('leases', function ($q) {
            $q->where('status', 'ACTIVE');
        })->count();
        $pendingTenants = Tenant::where('status', 'pending')->count();
        $inactiveTenants = $totalTenants - $activeTenants;

        return view('backend.layouts.tenants.tenant-list', compact(
            'totalTenants',
            'activeTenants',
            'pendingTenants',
            'inactiveTenants'
        ));
    }

    /**
     * Show tenant details
     */
    public function show($id)
    {
        // Get all tenants for sidebar
        $tenants = Tenant::with(['profile:id,tenant_id,first_name,middle_name,last_name,avatar'])
            ->select('id')
            ->orderBy('id', 'desc')
            ->get();

        // Get selected tenant with full details
        $tenant = Tenant::with([
            'profile',
            'leases' => function ($query) {
                $query->with([
                    'property',
                    'assignments.bed.room'
                ])->orderBy('start_date', 'desc');
            }
        ])->findOrFail($id);

        return view('backend.layouts.tenants.tenant-details', compact('tenant', 'tenants'));
    }


    /**
     * Get tenant details via AJAX
     */
    public function getTenantDetails($id)
    {
        try {
            $tenant = Tenant::with([
                'profile',
                'leases' => function ($query) {
                    $query->with([
                        'property',
                        'assignments.bed.room'
                    ])->orderBy('start_date', 'desc');
                }
            ])->findOrFail($id);

            $activeLease = $tenant->leases->where('status', 'ACTIVE')->first();

            $profile = $tenant->profile;
            $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')) : 'N/A';
            $avatar = $profile && $profile->avatar
                ? asset($profile->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

            return response()->json([
                'success' => true,
                'tenant' => [
                    'id' => $tenant->id,
                    'full_name' => $fullName,
                    'avatar' => $avatar,
                    'email' => $tenant->email,
                    'phone' => $profile->phone ?? 'N/A',
                    'status' => $tenant->status,
                    'tenant_since' => $tenant->created_at->format('M d, Y'),
                    'active_lease' => $activeLease ? [
                        'property_name' => $activeLease->property->name ?? 'N/A',
                        'unit' => $activeLease->assignments->first()->bed->bed_number ?? 'N/A',
                        'rent' => number_format($activeLease->rent_amount, 2),
                        'start_date' => date('M d, Y', strtotime($activeLease->start_date)),
                        'end_date' => date('M d, Y', strtotime($activeLease->end_date)),
                    ] : null,
                    'lease_history' => $tenant->leases->map(function ($lease) {
                        return [
                            'rent' => number_format($lease->rent_amount, 2),
                            'start_date' => date('M d, Y', strtotime($lease->start_date)),
                            'end_date' => date('M d, Y', strtotime($lease->end_date)),
                            'days_remaining' => max(0, now()->diffInDays($lease->end_date, false))
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }
    }


    /**
     * Delete tenant
     */
    public function destroy($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);

            // Check if tenant has active leases
            $hasActiveLeases = $tenant->leases()->where('status', 'ACTIVE')->exists();

            if ($hasActiveLeases) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete tenant with active leases!'
                ], 422);
            }

            $tenant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tenant deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenant: ' . $e->getMessage()
            ], 500);
        }
    }
}
