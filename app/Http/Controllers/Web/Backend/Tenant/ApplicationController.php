<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{
    public function index()
    {
        // dd('ApplicationController index method called');
        // Get statistics for cards
        $totalTenants = Tenant::where('status', 'rejected')->orWhere('status', 'pending')->count();
        $totalRjected = Tenant::where('status', 'rejected')->count();
        $pendingTenants = Tenant::where('status', 'pending')->count();

        return view('backend.layouts.tenants.applications.index', compact(
            'totalTenants',
            'totalRjected',
            'pendingTenants',
        ));
    }

    public function getData(Request $request)
    {
        // Fix browser back/forward button issue - only return JSON for AJAX requests
        if ($request->ajax() && $request->wantsJson()) {
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
                                        ->with('bed:id,bed_number,bed_label,room_id')
                                        ->limit(1);
                                }
                            ]);
                    }
                ])
                ->where('status', '!=','approved') // Only applications that are not yet approved
                ->where('status', '!=','active') // or active tenants
                ->orderBy('tenants.id', 'desc');

            // Status filter
            if ($request->filled('status')) {
                $query->where('tenants.status', $request->status);
            }

            // Application source filter
            if ($request->filled('tenant')) {
                $query->where(function ($q) use ($request) {
                    $keyword = $request->tenant;
                    $q->whereHas('profile', function ($q2) use ($keyword) {
                        $q2->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%")
                           ->orWhere('phone', 'like', "%{$keyword}%");
                    })
                    ->orWhere('email', 'like', "%{$keyword}%");
                });
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
                                    <div class="avatar avatar-md rounded-circle bg-secondary flex-shrink-0">
                                        <span class="text-white">N/A</span>
                                    </div>
                                    <div class="ms-3 text-truncate">
                                        <div class="fw-semibold text-truncate">No Profile</div>
                                        <small class="text-muted">Not Available</small>
                                    </div>
                                </div>';
                    }

                    $fullName = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    $avatar = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-3 flex-shrink-0" width="40" height="40" style="object-fit: cover;">
                                <div class="text-truncate">
                                    <div class="fw-semibold text-truncate" title="' . e($fullName) . '">' . e($fullName) . '</div>
                                    <small class="text-muted text-truncate d-block" title="' . e($profile->phone ?? 'No phone') . '">' . e($profile->phone ?? 'No phone') . '</small>
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
                        ? 'Unit-> ' . e($assignment->bed->bed_label)
                        : 'N/A';

                    return '<div class="text-truncate">
                                <div class="fw-semibold text-truncate" title="' . $propertyName . '">' . $propertyName . '</div>
                                <small class="text-muted text-truncate d-block" title="' . $unitInfo . '">' . $unitInfo . '</small>
                            </div>';
                })
                ->addColumn('address', function ($data) {
                    $activeLease = $data->leases->where('status', 'ACTIVE')->first();

                    if (!$activeLease || !$activeLease->property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    return '<small class="text-muted">Property Address</small>';
                })
                ->addColumn('account_status', function ($data) {
                    $hasActiveLease = $data->leases->where('status', 'ACTIVE')->isNotEmpty();

                    if ($hasActiveLease) {
                        return '<span class="badge p-3 bg-success">Active</span>';
                    }

                    return '<span class="badge p-3 bg-secondary">Inactive</span>';
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
                ->addColumn('action', function ($data) {
                    $btn = '';

                    if ($data->status === 'pending') {
                        $btn .= '<div class="btn-group" role="group">
                                
                                <button type="button" onclick="approveTenant(' . $data->id . ')" class="btn btn-info" title="Approve Tenant">
                                    <i class="fe fe-like"></i> Approve
                                </button>
                                <button type="button" onclick="rejectTenant(' . $data->id . ')" class="btn btn-danger" title="Reject Tenant">
                                    <i class="fe fe-dislike"></i> Reject
                                </button>
                            </div>';
                    }

                    return $btn;
                })
                ->rawColumns(['name', 'property_unit', 'address', 'account_status', 'tenant_status', 'rent', 'action'])
                ->make(true);
        }

        return abort(404);
    }

    public function approve($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status !== 'pending') {
            return response()->json(['message' => 'Only pending applications can be approved.'], 400);
        }

        $tenant->status = 'approved';
        $tenant->save();

        return response()->json(['message' => 'Tenant approved successfully.']);
    }

    public function reject($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status !== 'pending') {
            return response()->json(['message' => 'Only pending applications can be rejected.'], 400);
        }

        $tenant->status = 'rejected';
        $tenant->save();

        return response()->json(['message' => 'Tenant rejected successfully.']);
    }
}
