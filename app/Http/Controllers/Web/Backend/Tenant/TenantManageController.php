<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Models\Tenant;
use App\Models\TenantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TenantManageController extends Controller
{

    /**
     * Tenant lsit page
     */
    public function index(Request $request)
    {
        // Get statistics for cards
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereHas('leases', function ($q) {
            $q->where('status', 'ACTIVE');
        })->count();
        $pendingTenants = Tenant::where('status', 'pending')->count();
        $inactiveTenants = $totalTenants - $activeTenants;
        $properties = Property::select('id', 'name')->orderBy('name')->get();

        return view('backend.layouts.tenants.tenant-list', compact(
            'totalTenants',
            'activeTenants',
            'pendingTenants',
            'inactiveTenants',
            'properties'
        ));
    }

    /**
     * All tenant list
     */
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
                        $query->select('id', 'tenant_id', 'property_id', 'status', 'start_date', 'end_date', 'rent_amount') // Added property_id
                            ->whereNull('deleted_at')
                            ->with([
                                'property:id,name',
                                'assignments' => function ($q) {
                                    $q->select('id', 'lease_id', 'bed_id', 'is_current')
                                        ->where('is_current', true)
                                        ->whereNull('deleted_at')
                                        ->with('bed:id,bed_label,room_id')
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

            if($request->filled('property_id')) {
                $propertyId = $request->property_id;
                $query->whereHas('leases', function ($q) use ($propertyId) {
                    $q->where('property_id', $propertyId)
                      ->whereNull('deleted_at');
                });
            }

            if($request->filled('bed_id')) {
                $bedId = $request->bed_id;
                $query->whereHas('leases.assignments', function ($q) use ($bedId) {
                    $q->where('bed_id', $bedId)
                      ->where('is_current', true)
                      ->whereNull('deleted_at');
                });
            }

            if($request->filled('tenant')){
                $tenantKeyword = $request->tenant;
                $query->whereHas('profile', function ($q) use ($tenantKeyword) {
                    $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$tenantKeyword}%")
                    ->orWhere('phone', 'like', "%{$tenantKeyword}%")
                    ->orWhere('email', 'like', "%{$tenantKeyword}%");
                });
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
                        ? 'Bed: ' . e($assignment->bed->bed_label)
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
                // ->addColumn('roommates', function ($data) {
                //     $activeLease = $data->leases->where('status', 'ACTIVE')->first();

                //     if (!$activeLease) {
                //         return '<span class="text-muted">0</span>';
                //     }

                //     $assignment = $activeLease->assignments->first();
                //     if (!$assignment || !$assignment->bed) {
                //         return '<span class="text-muted">0</span>';
                //     }

                //     $roomId = $assignment->bed->room_id;
                //     $roommatesCount = DB::table('lease_assignments')
                //         ->join('leases', 'lease_assignments.lease_id', '=', 'leases.id')
                //         ->join('beds', 'lease_assignments.bed_id', '=', 'beds.id')
                //         ->where('beds.room_id', $roomId)
                //         ->where('lease_assignments.is_current', true)
                //         ->where('leases.status', 'ACTIVE')
                //         ->where('leases.tenant_id', '!=', $data->id)
                //         ->whereNull('lease_assignments.deleted_at')
                //         ->whereNull('leases.deleted_at')
                //         ->count();

                //     return '<span>' . $roommatesCount . '</span>';
                // })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('tenants.show', $data->id) . '" class="btn btn-primary" title="View Details">
                                    <i class="fe fe-eye"></i>
                                </a>
                                <button type="button" onclick="editTenant(' . $data->id . ')" class="btn btn-info" title="Edit Tenant">
                                    <i class="fe fe-edit"></i>
                                </button>
                                <button type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger" title="Delete Tenant">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['name', 'property_unit', 'address', 'account_status', 'tenant_status', 'rent', 'action'])
                ->make(true);
        }
    }


    /**
     * Store new tenant
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'middle_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create tenant
            $tenant = Tenant::create([
                'email' => $request->email,
                'status' => 'pending',
                'application_source' => 'admin',
                'password' => Hash::make('password123'), // Default password
                'status' => 'approved',
            ]);

            // Create tenant profile
            TenantProfile::create([
                'tenant_id' => $tenant->id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant for editing
     */
    public function edit($id)
    {
        try {
            $tenant = Tenant::with('profile')->findOrFail($id);

            return response()->json([
                'success' => true,
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'first_name' => $tenant->profile->first_name ?? '',
                    'middle_name' => $tenant->profile->middle_name ?? '',
                    'last_name' => $tenant->profile->last_name ?? '',
                    'phone' => $tenant->profile->phone ?? '',
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
     * Update tenant
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'middle_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $tenant = Tenant::findOrFail($id);
            $tenant->update([
                'email' => $request->email,
            ]);

            // Update or create profile
            TenantProfile::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tenant updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show tenant details
     */
    public function show($id)
    {
        $tenants = Tenant::with(['profile:id,tenant_id,first_name,middle_name,last_name,avatar'])
            ->select('id')
            ->orderBy('id', 'desc')
            ->get();

        $tenant = Tenant::with([
            'profile',
            'address',
            'emergencyContacts',
            'leases' => function ($query) {
                $query->with([
                    'property',
                    'assignments.bed.room.unit'
                ])->orderBy('start_date', 'desc');
            }
        ])->findOrFail($id);

        // Get all invoices for this tenant
        $invoices = \App\Models\Invoice::where('tenant_id', $id)
            ->with(['lease.property'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Calculate invoice statistics
        $invoiceStats = [
            'total' => $invoices->count(),
            'paid' => $invoices->where('status', 'PAID')->count(),
            'unpaid' => $invoices->whereIn('status', ['UNPAID', 'PENDING'])->count(),
            'overdue' => $invoices->filter(fn($inv) => $inv->isOverdue())->count(),
            'partial' => $invoices->where('status', 'PARTIAL')->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'balance_due' => $invoices->sum('balance_due'),
        ];

        return view('backend.layouts.tenants.tenant-details', compact('tenant', 'tenants', 'invoices', 'invoiceStats'));
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

    /**
     * Get active tenants for dropdown
     */
    public function getActiveTenants(Request $request)
    {
        try {
            $tenants = Tenant::where('status', 'approved')
                ->with('profile:id,tenant_id,first_name,last_name,phone')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($tenant) {
                    return [
                        'id' => $tenant->id,
                        'email' => $tenant->email,
                        'first_name' => $tenant->profile->first_name ?? 'N/A',
                        'last_name' => $tenant->profile->last_name ?? '',
                        'phone' => $tenant->profile->phone ?? '',
                        'status' => ucfirst($tenant->status)
                    ];
                });
            return response()->json([
                'success' => true,
                'data' => $tenants
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching active tenants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tenants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick create tenant for lease assignment
     */
    public function quickCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:tenants,email',
                'phone' => 'required|string|max:20'
            ]);

            DB::beginTransaction();

            // Create tenant
            $tenant = Tenant::create([
                'email' => $validated['email'],
                'status' => 'approved',
                'application_source' => 'admin'
            ]);

            // Create tenant profile
            $tenant->profile()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                // 'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            DB::commit();

            // Return tenant with profile
            $tenant->load('profile');

            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'first_name' => $tenant->profile->first_name,
                    'last_name' => $tenant->profile->last_name,
                    'phone' => $tenant->profile->phone,
                    'status' => ucfirst($tenant->status)
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant: ' . $e->getMessage()
            ], 500);
        }
    }
}
