<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestAttachment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
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
                    'maintenance_requests.unit_id',
                    'maintenance_requests.room_id',
                    'maintenance_requests.bed_id',
                    'maintenance_requests.unit',
                    'maintenance_requests.title',
                    'maintenance_requests.category',
                    'maintenance_requests.description',
                    'maintenance_requests.is_urgent',
                    'maintenance_requests.status',
                    'maintenance_requests.created_at'
                ])
                ->with([
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name,phone,avatar',
                        'activeLease:id,tenant_id,property_id,status,start_date,end_date,rent_amount' => [
                            'property:id,name,address',
                            'currentAssignment.bed.room.unit',
                        ]
                    ],
                    'property:id,name',
                    'unitModel:id,name',
                    'room:id,room_number,name',
                    'bed:id,bed_number,bed_label',
                    'attachments:id,maintenance_request_id,attachment_path',
                ])
                ->orderBy('maintenance_requests.is_urgent', 'desc')
                ->orderBy('maintenance_requests.id', 'desc');

            // Filters
            if ($request->filled('status')) {
                $query->where('maintenance_requests.status', $request->status);
            }
            if ($request->filled('property_id')) {
                $query->where(function ($q) use ($request) {
                    $q->where('maintenance_requests.property_id', $request->property_id)
                        ->orWhereHas(
                            'tenant.activeLease',
                            fn($lq) =>
                            $lq->where('property_id', $request->property_id)
                        );
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
                            DB::raw("CONCAT(first_name,' ',COALESCE(middle_name,''),' ',COALESCE(last_name,''))"),
                            'like',
                            "%{$keyword}%"
                        );
                    });
                })
                ->filterColumn('property', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereHas('property', fn($pq) => $pq->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('tenant.activeLease.property', fn($lq) => $lq->where('name', 'like', "%{$keyword}%"));
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
                    $color  = $statusColors[$data->status] ?? 'secondary';
                    $label  = $statusLabels[$data->status] ?? $data->status;
                    $urgent = $data->is_urgent
                        ? '<span class="badge p-3 bg-danger-transparent text-danger ms-1">Urgent</span>'
                        : '';
                    return '<span class="badge p-3 bg-' . $color . ' status-badge" data-id="' . $data->id . '">'
                        . $label . '</span>' . $urgent;
                })
                ->addColumn('request_info', function ($data) {
                    $icons = [
                        'ac'         => 'fe-wind',
                        'appliance'  => 'fe-box',
                        'electrical' => 'fe-zap',
                        'heat'       => 'fe-thermometer',
                        'kitchen'    => 'fe-coffee',
                        'plumbing'   => 'fe-droplet',
                        'other'      => 'fe-more-horizontal'
                    ];
                    $icon = $icons[$data->category] ?? 'fe-tool';
                    return '<div>
                            <div class="fw-semibold">' . e($data->title) . '</div>
                            <small class="text-muted">
                                <i class="fe ' . $icon . ' me-1"></i>' . ucfirst($data->category) . '
                            </small>
                        </div>';
                })
                ->addColumn('property_unit', function ($data) {
                    // Priority 1: direct foreign keys on maintenance request
                    $property = $data->property;
                    $unit     = $data->unitModel;
                    $room     = $data->room;
                    $bed      = $data->bed;

                    // Priority 2: fallback to tenant's active lease assignment
                    if (!$property) {
                        $activeLease = $data->tenant?->activeLease;
                        $property    = $activeLease?->property;
                        $assignment  = $activeLease?->currentAssignment;
                        $bed         = $bed  ?? $assignment?->bed;
                        $room        = $room ?? $bed?->room;
                        $unit        = $unit ?? $room?->unit;
                    }

                    if (!$property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    $hierarchy = '';
                    $parts     = [];
                    if ($unit) $parts[] = '<span class="hier-mini chip-unit">' . e($unit->name) . '</span>';
                    if ($room) $parts[] = '<span class="hier-mini chip-room">Rm ' . e($room->room_number) . '</span>';
                    if ($bed)  $parts[] = '<span class="hier-mini chip-bed">' . e($bed->bed_label ?? 'Bed ' . $bed->bed_number) . '</span>';

                    if (!empty($parts)) {
                        $hierarchy = '<div class="mt-1 d-flex flex-wrap gap-1">'
                            . implode('<i class="fe fe-chevron-right text-muted" style="font-size:10px;line-height:20px;"></i>', $parts)
                            . '</div>';
                    }

                    return '<div>
                            <div class="fw-semibold">' . e($property->name) . '</div>
                            ' . $hierarchy . '
                        </div>';
                })
                ->addColumn('lease_info', function ($data) {
                    $activeLease = $data->tenant?->activeLease;
                    if (!$activeLease) {
                        return '<span class="text-muted small">—</span>';
                    }
                    $colors = [
                        'ACTIVE'              => 'success',
                        'PENDING_TENANT_SIGN' => 'warning',
                        'PENDING_ADMIN_SIGN'  => 'info',
                        'DRAFT'               => 'secondary',
                        'TERMINATED'          => 'danger',
                        'COMPLETED'           => 'primary',
                    ];
                    $color = $colors[$activeLease->status] ?? 'secondary';
                    $label = str_replace('_', ' ', $activeLease->status);
                    return '<span class="badge p-3 bg-' . $color . '-transparent text-' . $color . '">' . $label . '</span>
                        <div class="small text-success fw-semibold mt-1">$' . number_format($activeLease->rent_amount, 2) . '/mo</div>';
                })
                ->addColumn('tenant_name', function ($data) {
                    if (!$data->tenant || !$data->tenant->profile) {
                        return '<span class="text-muted">No Tenant</span>';
                    }
                    $profile  = $data->tenant->profile;
                    $fullName = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    $avatar   = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';
                    return '<div class="d-flex align-items-center">
                            <img src="' . $avatar . '" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover;">
                            <span>' . e($fullName) . '</span>
                        </div>';
                })
                ->addColumn('issue_date', fn($data) => date('M d, Y', strtotime($data->created_at)))
                ->addColumn('actions', function ($data) {
                    return '<div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-primary" onclick="viewDetails(' . $data->id . ')" title="View Details">
                                <i class="fe fe-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-info" onclick="printMaintenance(' . $data->id . ')" title="Print PDF">
                                <i class="fe fe-printer"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" onclick="editRequest(' . $data->id . ')" title="Edit">
                                <i class="fe fe-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="quickStatus(' . $data->id . ')"
                                title="Change Status">
                                <i class="fe fe-refresh-cw"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteRequest(' . $data->id . ')" title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['status_badge', 'request_info', 'property_unit', 'lease_info', 'tenant_name', 'actions'])
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
                'property_id'   => $lease->property_id,
                'property_name' => $lease->property?->name    ?? 'N/A',
                'address'       => $lease->property?->address ?? '',
                'unit_id'       => $unit?->id,
                'unit_name'     => $unit?->name,
                'room_id'       => $room?->id,
                'room_number'   => $room?->room_number,
                'room_name'     => $room?->name ?? ('Room ' . $room?->room_number),
                'bed_id'        => $bed?->id,
                'bed_number'    => $bed?->bed_number,
                'bed_label'     => $bed?->bed_label,
                'base_rent'     => $bed?->base_rent,
                'status'        => $lease->status,
                'start_date'    => $lease->start_date,
                'end_date'      => $lease->end_date,
                'rent_amount'   => $lease->rent_amount,
            ];
        });

        return response()->json(['success' => true, 'leases' => $data]);
    }

    /**
     * When you select a property, load the units of that property.
     */
    public function getPropertyUnits(Request $request)
    {
        $request->validate(['property_id' => 'required|exists:properties,id']);

        $units = Unit::where('property_id', $request->property_id)
            ->where('is_active', true)
            ->select('id', 'name', 'gender_designation')
            ->get();

        return response()->json(['success' => true, 'units' => $units]);
    }

    /**
     * When you select a unit, load the rooms of that unit.
     */
    public function getUnitRooms(Request $request)
    {
        $request->validate(['unit_id' => 'required|exists:units,id']);

        $rooms = Room::where('unit_id', $request->unit_id)
            ->where('is_active', true)
            ->select('id', 'room_number', 'name', 'gender_designation')
            ->get()
            ->map(fn($r) => [
                'id'     => $r->id,
                'name'   => $r->name ?? ('Room ' . $r->room_number),
                'number' => $r->room_number,
            ]);

        return response()->json(['success' => true, 'rooms' => $rooms]);
    }

    /**
     * When you select a room, load the beds of that room.
     */
    public function getRoomBeds(Request $request)
    {
        $request->validate(['room_id' => 'required|exists:rooms,id']);

        $beds = Bed::where('room_id', $request->room_id)
            ->select('id', 'bed_number', 'bed_label', 'base_rent', 'is_occupied')
            ->get()
            ->map(fn($b) => [
                'id'          => $b->id,
                'label'       => $b->bed_label ?? ('Bed ' . $b->bed_number),
                'bed_number'  => $b->bed_number,
                'base_rent'   => $b->base_rent,
                'is_occupied' => $b->is_occupied,
            ]);

        return response()->json(['success' => true, 'beds' => $beds]);
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
            'title'           => 'required|string|max:255',
            'category'        => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'     => 'required|string',
            'tenant_id'       => 'required|exists:tenants,id',
            'property_id'     => 'nullable|exists:properties,id',
            'unit_id'         => 'nullable|exists:units,id',
            'room_id'         => 'nullable|exists:rooms,id',
            'bed_id'          => 'nullable|exists:beds,id',
            'is_urgent'       => 'boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,bmp,jfif,mp4,mov,webm,mpeg,m4v|max:20480'
        ]);

        try {
            DB::beginTransaction();

            // If property_id does not exist, take it from the tenant's active lease.
            $propertyId = $request->property_id;
            $unitId     = $request->unit_id;
            $roomId     = $request->room_id;
            $bedId      = $request->bed_id;

            if (!$propertyId) {
                $activeLease = Lease::where('tenant_id', $request->tenant_id)
                    ->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN'])
                    ->with('currentAssignment.bed.room.unit')
                    ->latest()
                    ->first();

                if ($activeLease) {
                    $propertyId = $activeLease->property_id;
                    $bed        = $activeLease->currentAssignment?->bed;
                    $unitId     = $unitId  ?? $bed?->room?->unit?->id;
                    $roomId     = $roomId  ?? $bed?->room?->id;
                    $bedId      = $bedId   ?? $bed?->id;
                }
            }

            $maintenance = MaintenanceRequest::create([
                'tenant_id'        => $request->tenant_id,
                'property_id'      => $propertyId,
                'unit_id'          => $unitId,
                'room_id'          => $roomId,
                'bed_id'           => $bedId,
                'unit'             => $request->unit, // legacy string field
                'title'            => $request->title,
                'category'         => $request->category,
                'description'      => $request->description,
                'is_urgent'        => $request->boolean('is_urgent'),
                'grant_permission' => $request->grant_permission,
                'status'           => 'pending',
                'created_by'       => auth()->id()
            ]);

            // if ($request->hasFile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         $path = $file->store('maintenance_attachments', 'public');
            //         MaintenanceRequestAttachment::create([
            //             'maintenance_request_id' => $maintenance->id,
            //             'attachment_path'        => $path
            //         ]);
            //     }
            // }

            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                if (!is_array($files)) $files = [$files];

                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $path = Helper::uploadImage($file, 'maintenance_attachments');
                        MaintenanceRequestAttachment::create([
                            'maintenance_request_id' => $maintenance->id,
                            'attachment_path'        => $path,
                        ]);
                    }
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
                'message' => 'Failed: ' . $e->getMessage()
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
            'tenant.activeLease.currentAssignment.bed.room.unit',
            'property',
            'unitModel',
            'room',
            'bed',
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
            'title'           => 'required|string|max:255',
            'category'        => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'     => 'required|string',
            'tenant_id'       => 'required|exists:tenants,id',
            'property_id'     => 'nullable|exists:properties,id',
            'unit_id'         => 'nullable|exists:units,id',
            'room_id'         => 'nullable|exists:rooms,id',
            'bed_id'          => 'nullable|exists:beds,id',
            'status'          => 'required|in:pending,in_progress,completed,rejected,cancelled',
            'is_urgent'       => 'boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,bmp,jfif,mp4,mov,webm,mpeg,m4v|max:20480'
        ]);

        try {
            DB::beginTransaction();

            $maintenance = MaintenanceRequest::findOrFail($id);

            $propertyId = $request->property_id;
            $unitId     = $request->unit_id;
            $roomId     = $request->room_id;
            $bedId      = $request->bed_id;

            // Fallback to lease if not provided
            if (!$propertyId) {
                $activeLease = Lease::where('tenant_id', $request->tenant_id)
                    ->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN'])
                    ->with('currentAssignment.bed.room.unit')
                    ->latest()->first();

                if ($activeLease) {
                    $propertyId = $activeLease->property_id;
                    $bed        = $activeLease->currentAssignment?->bed;
                    $unitId     = $unitId  ?? $bed?->room?->unit?->id;
                    $roomId     = $roomId  ?? $bed?->room?->id;
                    $bedId      = $bedId   ?? $bed?->id;
                }
            }

            $maintenance->update([
                'tenant_id'        => $request->tenant_id,
                'property_id'      => $propertyId,
                'unit_id'          => $unitId,
                'room_id'          => $roomId,
                'bed_id'           => $bedId,
                'title'            => $request->title,
                'category'         => $request->category,
                'description'      => $request->description,
                'is_urgent'        => $request->boolean('is_urgent'),
                'grant_permission' => $request->grant_permission,
                'status'           => $request->status,
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
    public function show($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'tenant.activeLease.currentAssignment.bed.room.unit',
            'property',
            'unitModel',
            'room',
            'bed',
            'attachments',
            'creator'
        ])->findOrFail($id);

        $maintenanceRequests = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'tenant.activeLease.currentAssignment.bed.room.unit',
            'property',
            'unitModel',
            'room',
            'bed',
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

    /**
     * Mark as resolved
     */
    public function markAsResolved(Request $request, $id)
    {
        try {
            $maintenance = MaintenanceRequest::findOrFail($id);
            $maintenance->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request marked as resolved.',
                'status'  => 'completed',
                'label'   => 'Resolved',
                'color'   => 'success',
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Quick status update
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,rejected,cancelled'
        ]);

        try {
            $maintenance = MaintenanceRequest::findOrFail($id);
            $maintenance->update(['status' => $request->status]);

            $labels = [
                'pending'     => ['label' => 'Open',        'color' => 'primary'],
                'in_progress' => ['label' => 'In Progress',  'color' => 'warning'],
                'completed'   => ['label' => 'Resolved',     'color' => 'success'],
                'rejected'    => ['label' => 'Rejected',     'color' => 'danger'],
                'cancelled'   => ['label' => 'Cancelled',    'color' => 'secondary'],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status'  => $request->status,
                'label'   => $labels[$request->status]['label'],
                'color'   => $labels[$request->status]['color'],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export Maintenance Detail to PDF
     */
    public function exportPdf($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'tenant.activeLease.property',
            'tenant.activeLease.currentAssignment.bed.room.unit',
            'property',
            'unitModel',
            'room',
            'bed',
            'attachments',
            'creator'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('backend.layouts.maintenance.detail-pdf', compact('maintenance'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Maintenance-Request-' . $maintenance->id . '.pdf';
        return $pdf->stream($filename);
    }
}
