<?php

namespace App\Http\Controllers\Web\Backend\Tenant;


use Exception;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MaintenanceRequestAttachment;

class MaintananceController extends Controller
{
    /**
     * Show Maintenance page
     */
    public function index(Request $request)
    {
        // Get statistics
        $stats = [
            'open' => MaintenanceRequest::where('status', 'pending')->count(),
            'resolved' => MaintenanceRequest::where('status', 'completed')->count(),
            'scheduled' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'urgent' => MaintenanceRequest::where('is_urgent', true)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count()
        ];

        return view('backend.layouts.maintenance.index', compact('stats'));
    }

    /**
     * Maintenance list
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
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name,phone,avatar'
                    ],
                    'property:id,name',
                    'attachments:id,maintenance_request_id,attachment_path'
                ])
                ->orderBy('maintenance_requests.is_urgent', 'desc')
                ->orderBy('maintenance_requests.id', 'desc');

            // Status filter
            if ($request->filled('status')) {
                $query->where('maintenance_requests.status', $request->status);
            }

            // Property filter
            if ($request->filled('property_id')) {
                $query->where('maintenance_requests.property_id', $request->property_id);
            }

            // Category filter
            if ($request->filled('category')) {
                $query->where('maintenance_requests.category', $request->category);
            }

            // Urgent filter
            if ($request->filled('urgent')) {
                $query->where('maintenance_requests.is_urgent', true);
            }

            // Date range filter
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
                        $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('property', function ($query, $keyword) {
                    $query->whereHas('property', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('status_badge', function ($data) {
                    $statusColors = [
                        'pending' => 'primary',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'secondary'
                    ];

                    $statusLabels = [
                        'pending' => 'Open',
                        'in_progress' => 'In Progress',
                        'completed' => 'Resolved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled'
                    ];

                    $color = $statusColors[$data->status] ?? 'secondary';
                    $label = $statusLabels[$data->status] ?? $data->status;

                    $urgentBadge = $data->is_urgent ? '<span class="badge p-3 bg-danger-transparent text-danger ms-1"> Urgent</span>' : '';

                    return '<span class="badge p-3 bg-' . $color . '">' . $label . '</span>' . $urgentBadge;
                })
                ->addColumn('request_info', function ($data) {
                    $categoryIcons = [
                        'ac' => 'fe-wind',
                        'appliance' => 'fe-box',
                        'electrical' => 'fe-zap',
                        'heat' => 'fe-thermometer',
                        'kitchen' => 'fe-coffee',
                        'plumbing' => 'fe-droplet',
                        'other' => 'fe-more-horizontal'
                    ];

                    $icon = $categoryIcons[$data->category] ?? 'fe-tool';
                    $category = ucfirst($data->category);

                    return '<div>
                                <div class="fw-semibold">' . e($data->title) . '</div>
                                <small class="text-muted"><i class="fe ' . $icon . ' me-1"></i>' . $category . '</small>
                            </div>';
                })
                ->addColumn('property_unit', function ($data) {
                    if (!$data->property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    $unitInfo = $data->unit ? e($data->unit) : 'N/A';

                    return '<div>
                                <div class="fw-semibold">' . e($data->property->name) . '</div>
                                <small class="text-muted">Unit: ' . $unitInfo . '</small>
                            </div>';
                })
                ->addColumn('tenant_name', function ($data) {
                    if (!$data->tenant || !$data->tenant->profile) {
                        return '<span class="text-muted">No Tenant</span>';
                    }

                    $profile = $data->tenant->profile;
                    $fullName = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    $avatar = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                <span>' . e($fullName) . '</span>
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
                ->rawColumns(['status_badge', 'request_info', 'property_unit', 'tenant_name', 'actions'])
                ->make(true);
        }
    }


    /**
     * Maintenance create page
     */
    public function create()
    {
        $properties = Property::all();
        $tenants = Tenant::with(['profile'])->where('status', 'active')->get();

        return view('backend.layouts.maintenance.create', compact('properties', 'tenants'));
    }

    /**
     * Maintenance store
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description' => 'required|string',
            'tenant_id' => 'required|exists:tenants,id',
            'is_urgent' => 'boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,bmp,jfif,mp4,mov,webm,mpeg,m4v|max:20480'
        ]);

        try {
            DB::beginTransaction();

            $maintenance = MaintenanceRequest::create([
                'tenant_id' => $request->tenant_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'is_urgent' => $request->has('is_urgent') ? true : false,
                'grant_permission' => $request->grant_permission,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            // Handle file uploads
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('maintenance_attachments', 'public');

                    MaintenanceRequestAttachment::create([
                        'maintenance_request_id' => $maintenance->id,
                        'attachment_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request created successfully',
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
     * Maintenance edit
     */
    public function edit($id)
    {
        $maintenance = MaintenanceRequest::with(['tenant.profile', 'property', 'attachments'])->findOrFail($id);
        $properties = Property::all();
        $tenants = Tenant::with(['profile'])->where('status', 'active')->get();

        return view('backend.layouts.maintenance.edit', compact('maintenance', 'properties', 'tenants'));
    }

    /**
     * Maintenance edit
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description' => 'required|string',
            'tenant_id' => 'required|exists:tenants,id',
            'status' => 'required|in:pending,in_progress,completed,rejected,cancelled',
            'is_urgent' => 'boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,bmp,jfif,mp4,mov,webm,mpeg,m4v|max:20480'
        ]);

        try {
            DB::beginTransaction();

            $maintenance = MaintenanceRequest::findOrFail($id);

            $maintenance->update([
                'tenant_id' => $request->tenant_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'is_urgent' => $request->has('is_urgent') ? true : false,
                'grant_permission' => $request->grant_permission,
                'status' => $request->status
            ]);

            // Handle file uploads
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('maintenance_attachments', 'public');

                    MaintenanceRequestAttachment::create([
                        'maintenance_request_id' => $maintenance->id,
                        'attachment_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request updated successfully',
                'redirect' => route('maintanance.index')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maintenance delete
     */
    public function destroy($id)
    {
        try {
            $maintenance = MaintenanceRequest::findOrFail($id);
            $maintenance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete maintenance request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maintenance show
     */
    public function show($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'property',
            'attachments',
            'creator'
        ])->findOrFail($id);

        // Get all maintenance requests for sidebar
        $maintenanceRequests = MaintenanceRequest::with([
            'tenant.profile',
            'property'
        ])
            ->orderBy('is_urgent', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.layouts.maintenance.detail', compact('maintenance', 'maintenanceRequests'));
    }

    /**
     * Maintenance details
     */
    public function details($id)
    {
        $maintenance = MaintenanceRequest::with([
            'tenant.profile',
            'property',
            'attachments',
            'creator'
        ])->findOrFail($id);

        // Format tenant info
        $profile = $maintenance->tenant ? $maintenance->tenant->profile : null;
        $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')) : 'No Tenant';
        $avatar = $profile && $profile->avatar
            ? asset($profile->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

        return response()->json([
            'success' => true,
            'maintenance' => [
                'id' => $maintenance->id,
                'title' => $maintenance->title,
                'status' => $maintenance->status,
                'category' => $maintenance->category,
                'is_urgent' => $maintenance->is_urgent,
                'grant_permission' => $maintenance->grant_permission,
                'description' => $maintenance->description,
                'tenant_name' => $fullName,
                'tenant_email' => $maintenance->tenant ? $maintenance->tenant->email : 'N/A',
                'tenant_phone' => $profile ? $profile->phone : 'N/A',
                'avatar' => $avatar,
                'property_name' => $maintenance->property ? $maintenance->property->name : 'N/A',
                'unit' => $maintenance->unit ?? 'N/A',
                'address' => $maintenance->property ? $maintenance->property->address . ', ' . $maintenance->property->city . ', ' . $maintenance->property->state . ' ' . $maintenance->property->zip_code : 'N/A',
                'requested_date' => date('M d, Y', strtotime($maintenance->created_at)),
                'attachments' => $maintenance->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'path' => asset('storage/' . $attachment->attachment_path),
                        'type' => pathinfo($attachment->attachment_path, PATHINFO_EXTENSION)
                    ];
                })
            ]
        ]);
    }
}
