<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestAttachment;
use App\Models\Property;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MaintananceController extends Controller
{
    /**
     * Show Maintenance page
     */
    public function index(Request $request)
    {
        $stats = [
            'open'      => MaintenanceRequest::where('status', 'pending')->count(),
            'resolved'  => MaintenanceRequest::where('status', 'completed')->count(),
            'scheduled' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'urgent'    => MaintenanceRequest::where('is_urgent', true)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count()
        ];

        return view('backend.layouts.maintenance.index', compact('stats'));
    }

    /**
     * Maintenance DataTable list
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MaintenanceRequest::query()
                ->select([
                    'maintenance_requests.id',
                    'maintenance_requests.tenant_id',
                    'maintenance_requests.property_id',
                    'maintenance_requests.unit',
                    'maintenance_requests.title',
                    'maintenance_requests.category',
                    'maintenance_requests.description',
                    'maintenance_requests.is_urgent',
                    'maintenance_requests.status',
                    'maintenance_requests.created_at'
                ])
                ->with([
                    // Tenant + profile + their active lease + that lease's property
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name,phone,avatar',
                        'activeLease:id,tenant_id,property_id,status,start_date,end_date,rent_amount' => [
                            'property:id,name,address'
                        ]
                    ],
                    // Direct property (if property_id is set on the request itself)
                    'property:id,name',
                    'attachments:id,maintenance_request_id,attachment_path',
                ])
                ->orderBy('maintenance_requests.is_urgent', 'desc')
                ->orderBy('maintenance_requests.id', 'desc');

            // Filters
            if ($request->filled('status')) {
                $query->where('maintenance_requests.status', $request->status);
            }

            if ($request->filled('property_id')) {
                // Filter by direct property OR by tenant's leased property
                $query->where(function ($q) use ($request) {
                    $q->where('maintenance_requests.property_id', $request->property_id)
                        ->orWhereHas('tenant.activeLease', function ($lq) use ($request) {
                            $lq->where('property_id', $request->property_id);
                        });
                });
            }

            if ($request->filled('category')) {
                $query->where('maintenance_requests.category', $request->category);
            }

            if ($request->filled('urgent')) {
                $query->where('maintenance_requests.is_urgent', true);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('maintenance_requests.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('maintenance_requests.created_at', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('tenant', function ($query, $keyword) {
                    $query->whereHas('tenant.profile', function ($q) use ($keyword) {
                        $q->where(
                            DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"),
                            'like',
                            "%{$keyword}%"
                        );
                    });
                })
                ->filterColumn('property', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        // Search in direct property
                        $q->whereHas('property', function ($pq) use ($keyword) {
                            $pq->where('name', 'like', "%{$keyword}%");
                        })
                            // OR search in tenant's leased property
                            ->orWhereHas('tenant.activeLease.property', function ($lq) use ($keyword) {
                                $lq->where('name', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->addColumn('status_badge', function ($data) {
                    $statusColors = [
                        'pending'     => 'primary',
                        'in_progress' => 'warning',
                        'completed'   => 'success',
                        'rejected'    => 'danger',
                        'cancelled'   => 'secondary'
                    ];

                    $statusLabels = [
                        'pending'     => 'Open',
                        'in_progress' => 'In Progress',
                        'completed'   => 'Resolved',
                        'rejected'    => 'Rejected',
                        'cancelled'   => 'Cancelled'
                    ];

                    $color = $statusColors[$data->status] ?? 'secondary';
                    $label = $statusLabels[$data->status] ?? $data->status;
                    $urgentBadge = $data->is_urgent
                        ? '<span class="badge p-3 bg-danger-transparent text-danger ms-1">Urgent</span>'
                        : '';

                    return '<span class="badge p-3 bg-' . $color . '">' . $label . '</span>' . $urgentBadge;
                })
                ->addColumn('request_info', function ($data) {
                    $categoryIcons = [
                        'ac'          => 'fe-wind',
                        'appliance'   => 'fe-box',
                        'electrical'  => 'fe-zap',
                        'heat'        => 'fe-thermometer',
                        'kitchen'     => 'fe-coffee',
                        'plumbing'    => 'fe-droplet',
                        'other'       => 'fe-more-horizontal'
                    ];

                    $icon = $categoryIcons[$data->category] ?? 'fe-tool';

                    return '<div>
                                <div class="fw-semibold">' . e($data->title) . '</div>
                                <small class="text-muted">
                                    <i class="fe ' . $icon . ' me-1"></i>' . ucfirst($data->category) . '
                                </small>
                            </div>';
                })
                ->addColumn('property_unit', function ($data) {
                    // Priority 1: Direct property_id set on maintenance request
                    if ($data->property) {
                        $unitInfo = $data->unit ? e($data->unit) : 'N/A';
                        return '<div>
                                    <div class="fw-semibold">' . e($data->property->name) . '</div>
                                    <small class="text-muted">Unit: ' . $unitInfo . '</small>
                                </div>';
                    }

                    // Priority 2: Fallback to tenant's active lease property
                    $activeLease = $data->tenant?->activeLease;

                    if ($activeLease && $activeLease->property) {
                        $leaseStatus = '<span class="badge bg-info-transparent text-info ms-1">Via Lease</span>';
                        return '<div>
                                    <div class="fw-semibold">' . e($activeLease->property->name) . $leaseStatus . '</div>
                                    <small class="text-muted">
                                        Lease: ' . Carbon::parse($activeLease->start_date)->format('M Y') .
                            ' – ' . Carbon::parse($activeLease->end_date)->format('M Y') . '
                                    </small>
                                </div>';
                    }

                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('tenant_name', function ($data) {
                    if (!$data->tenant || !$data->tenant->profile) {
                        return '<span class="text-muted">No Tenant</span>';
                    }

                    $profile  = $data->tenant->profile;
                    $fullName = trim(
                        $profile->first_name . ' ' .
                            ($profile->middle_name ?? '') . ' ' .
                            ($profile->last_name ?? '')
                    );
                    $avatar = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-2"
                                    width="32" height="32" style="object-fit: cover;">
                                <span>' . e($fullName) . '</span>
                            </div>';
                })
                ->addColumn('lease_info', function ($data) {
                    // Extra column — shows full lease details if needed
                    $activeLease = $data->tenant?->activeLease;

                    if (!$activeLease) {
                        return '<span class="badge p-3 bg-secondary">No Active Lease</span>';
                    }

                    $statusColors = [
                        'ACTIVE'             => 'success',
                        'PENDING_TENANT_SIGN' => 'warning',
                        'PENDING_ADMIN_SIGN'  => 'info',
                        'DRAFT'              => 'secondary',
                        'TERMINATED'         => 'danger',
                        'COMPLETED'          => 'primary',
                    ];

                    $color = $statusColors[$activeLease->status] ?? 'secondary';

                    return '<span class="badge p-3 bg-' . $color . '">' . $activeLease->status . '</span>
                            <div class="small text-muted mt-1">
                                $' . number_format($activeLease->rent_amount, 2) . '/mo
                            </div>';
                })
                ->addColumn('issue_date', function ($data) {
                    return date('M d, Y', strtotime($data->created_at));
                })
                ->addColumn('actions', function ($data) {
                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary" onclick="viewDetails(' . $data->id . ')">
                                    <i class="fe fe-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="editRequest(' . $data->id . ')">
                                    <i class="fe fe-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteRequest(' . $data->id . ')">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns([
                    'status_badge',
                    'request_info',
                    'property_unit',
                    'tenant_name',
                    'lease_info',
                    'actions'
                ])
                ->make(true);
        }
    }

    /**
     * Get tenant's leased properties — called via AJAX when tenant is selected in create/edit form
     */
    public function getTenantLeaseProperties(Request $request)
    {
        $request->validate(['tenant_id' => 'required|exists:tenants,id']);

        $leases = Lease::where('tenant_id', $request->tenant_id)
            ->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
            ->with([
                'property:id,name,address',
                'currentAssignment.bed.room.unit',
            ])
            ->select(
                'id',
                'tenant_id',
                'property_id',
                'status',
                'start_date',
                'end_date',
                'rent_amount'
            )
            ->get();

        if ($leases->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active lease found for this tenant.',
                'leases'  => []
            ]);
        }

        $data = $leases->map(function ($lease) {
            $assignment = $lease->currentAssignment;
            $bed        = $assignment?->bed;
            $room       = $bed?->room;
            $unit       = $room?->unit;

            return [
                'lease_id'      => $lease->id,
                // Property
                'property_id'   => $lease->property_id,
                'property_name' => $lease->property?->name  ?? 'N/A',
                'address'       => $lease->property?->address ?? 'N/A',
                // Unit
                'unit_id'       => $unit?->id,
                'unit_name'     => $unit?->name,
                // Room
                'room_id'       => $room?->id,
                'room_number'   => $room?->room_number,
                'room_name'     => $room?->name ?? ('Room ' . $room?->room_number),
                // Bed
                'bed_id'        => $bed?->id,
                'bed_number'    => $bed?->bed_number,
                'bed_label'     => $bed?->bed_label,
                'base_rent'     => $bed?->base_rent,
                // Assignment
                'move_in'       => $assignment?->actual_move_in,
                'assigned_at'   => $assignment?->assigned_at,
                // Lease
                'status'        => $lease->status,
                'start_date'    => $lease->start_date,
                'end_date'      => $lease->end_date,
                'rent_amount'   => $lease->rent_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'leases'  => $data
        ]);
    }
    /**
     * Maintenance create page
     */
    public function create()
    {
        $tenants = Tenant::with(['profile:id,tenant_id,first_name,last_name'])
            ->where('status', 'approved')
            ->select('id', 'email')
            ->limit(500)
            ->get();

        // Properties এখন tenant select করলে AJAX এ load হবে
        return view('backend.layouts.maintenance.create', compact('tenants'));
    }

    /**
     * Maintenance store
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'category'       => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'    => 'required|string',
            'tenant_id'      => 'required|exists:tenants,id',
            'property_id'    => 'nullable|exists:properties,id',
            'unit'           => 'nullable|string|max:255',
            'is_urgent'      => 'boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,bmp,jfif,mp4,mov,webm,mpeg,m4v|max:20480'
        ]);

        try {
            DB::beginTransaction();

            // property_id না দিলে tenant এর active lease থেকে নাও
            $propertyId = $request->property_id;
            if (!$propertyId) {
                $activeLease = Lease::where('tenant_id', $request->tenant_id)
                    ->where('status', 'ACTIVE')
                    ->latest()
                    ->first();

                $propertyId = $activeLease?->property_id;
            }

            $maintenance = MaintenanceRequest::create([
                'tenant_id'        => $request->tenant_id,
                'property_id'      => $propertyId,
                'unit'             => $request->unit,
                'title'            => $request->title,
                'category'         => $request->category,
                'description'      => $request->description,
                'is_urgent'        => $request->boolean('is_urgent'),
                'grant_permission' => $request->grant_permission,
                'status'           => 'pending',
                'created_by'       => auth()->id()
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('maintenance_attachments', 'public');
                    MaintenanceRequestAttachment::create([
                        'maintenance_request_id' => $maintenance->id,
                        'attachment_path'        => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Maintenance request created successfully',
                'redirect' => route('maintanance.index')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maintenance edit page
     */
    public function edit($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'property',
            'attachments'
        ])->findOrFail($id);

        $tenants = Tenant::with(['profile:id,tenant_id,first_name,last_name'])
            ->where('status', 'active')
            ->select('id', 'email')
            ->limit(500)
            ->get();

        return view('backend.layouts.maintenance.edit', compact('maintenance', 'tenants'));
    }

    /**
     * Maintenance update
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'category'       => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'    => 'required|string',
            'tenant_id'      => 'required|exists:tenants,id',
            'property_id'    => 'nullable|exists:properties,id',
            'unit'           => 'nullable|string|max:255',
            'status'         => 'required|in:pending,in_progress,completed,rejected,cancelled',
            'is_urgent'      => 'boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,bmp,jfif,mp4,mov,webm,mpeg,m4v|max:20480'
        ]);

        try {
            DB::beginTransaction();

            $maintenance = MaintenanceRequest::findOrFail($id);

            $propertyId = $request->property_id;
            if (!$propertyId) {
                $activeLease = Lease::where('tenant_id', $request->tenant_id)
                    ->where('status', 'ACTIVE')
                    ->latest()
                    ->first();
                $propertyId = $activeLease?->property_id;
            }

            $maintenance->update([
                'tenant_id'        => $request->tenant_id,
                'property_id'      => $propertyId,
                'unit'             => $request->unit,
                'title'            => $request->title,
                'category'         => $request->category,
                'description'      => $request->description,
                'is_urgent'        => $request->boolean('is_urgent'),
                'grant_permission' => $request->grant_permission,
                'status'           => $request->status
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('maintenance_attachments', 'public');
                    MaintenanceRequestAttachment::create([
                        'maintenance_request_id' => $maintenance->id,
                        'attachment_path'        => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Maintenance request updated successfully',
                'redirect' => route('maintanance.index')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maintenance delete
     */
    public function destroy($id)
    {
        try {
            MaintenanceRequest::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Maintenance show (detail page)
     */
    // public function show($id)
    // {
    //     $maintenance = MaintenanceRequest::with([
    //         'tenant.profile',
    //         'tenant.activeLease.property',
    //         'property',
    //         'attachments',
    //         'creator'
    //     ])->findOrFail($id);

    //     $maintenanceRequests = MaintenanceRequest::with([
    //         'tenant.profile',
    //         'tenant.activeLease.property',
    //         'property'
    //     ])
    //         ->orderBy('is_urgent', 'desc')
    //         ->orderBy('id', 'desc')
    //         ->get();

    //     return view('backend.layouts.maintenance.detail', compact('maintenance', 'maintenanceRequests'));
    // }

    public function show($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'tenant.activeLease.currentAssignment.bed.room.unit',
            'property',
            'attachments',
            'creator'
        ])->findOrFail($id);

        $maintenanceRequests = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'tenant.activeLease.currentAssignment.bed.room.unit',
            'property'
        ])
            ->orderBy('is_urgent', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view(
            'backend.layouts.maintenance.detail',
            compact('maintenance', 'maintenanceRequests')
        );
    }

    /**
     * Maintenance details (JSON for modal)
     */
    public function details($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'property',
            'attachments',
            'creator'
        ])->findOrFail($id);

        $profile  = $maintenance->tenant?->profile;
        $fullName = $profile
            ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
            : 'No Tenant';

        $avatar = ($profile && $profile->avatar)
            ? asset($profile->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

        // Property: direct or via lease
        $property    = $maintenance->property ?? $maintenance->tenant?->activeLease?->property;
        $activeLease = $maintenance->tenant?->activeLease;

        return response()->json([
            'success'     => true,
            'maintenance' => [
                'id'               => $maintenance->id,
                'title'            => $maintenance->title,
                'status'           => $maintenance->status,
                'category'         => $maintenance->category,
                'is_urgent'        => $maintenance->is_urgent,
                'grant_permission' => $maintenance->grant_permission,
                'description'      => $maintenance->description,
                'tenant_name'      => $fullName,
                'tenant_email'     => $maintenance->tenant?->email ?? 'N/A',
                'tenant_phone'     => $profile?->phone ?? 'N/A',
                'avatar'           => $avatar,
                'property_name'    => $property?->name ?? 'N/A',
                'property_address' => $property?->address ?? 'N/A',
                'unit'             => $maintenance->unit ?? 'N/A',
                'requested_date'   => date('M d, Y', strtotime($maintenance->created_at)),
                // Lease info
                'lease'            => $activeLease ? [
                    'id'               => $activeLease->id,
                    'status'           => $activeLease->status,
                    'start_date'       => $activeLease->start_date,
                    'end_date'         => $activeLease->end_date,
                    'rent_amount'      => $activeLease->rent_amount,
                    'deposit_amount'   => $activeLease->deposit_amount,
                    'payment_frequency' => $activeLease->payment_frequency,
                ] : null,
                'attachments'      => $maintenance->attachments->map(fn($a) => [
                    'id'   => $a->id,
                    'path' => asset('storage/' . $a->attachment_path),
                    'type' => pathinfo($a->attachment_path, PATHINFO_EXTENSION)
                ])
            ]
        ]);
    }
}
