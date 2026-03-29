<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Unit;
use App\Models\Lease;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\MaintenanceRequestAttachment;
use App\Http\Resources\Maintanace\MaintenanceRequestResource;

class MaintananceController extends Controller
{
    use ApiResponse;

    /*==========================================================
    | LEASE HIERARCHY ENDPOINTS
    | Tenant will select from their leased properties.
    ==========================================================*/

    /**
     * Tenant will select from their leased properties.
     * GET /api/maintanance/my-leases
     */
    public function myLeases(Request $request)
    {
        try {
            $tenant = auth('api')->user();

            $leases = Lease::where('tenant_id', $tenant->id)
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
                    'rent_amount',
                    'payment_frequency'
                )
                ->get();

            if ($leases->isEmpty()) {
                return $this->success([], 'No active lease found', 200);
            }

            $data = $leases->map(function ($lease) {
                $assignment = $lease->currentAssignment;
                $bed        = $assignment?->bed;
                $room       = $bed?->room;
                $unit       = $room?->unit;

                return [
                    'lease_id'    => $lease->id,
                    'status'      => $lease->status,
                    'start_date'  => $lease->start_date,
                    'end_date'    => $lease->end_date,
                    'rent_amount' => $lease->rent_amount,
                    'payment_frequency' => $lease->payment_frequency,

                    'property' => $lease->property ? [
                        'id'      => $lease->property->id,
                        'name'    => $lease->property->name,
                        'address' => $lease->property->address,
                    ] : null,

                    // Current assignment info
                    'assignment' => $assignment ? [
                        'assignment_id' => $assignment->id,
                        'assigned_at'   => $assignment->assigned_at,
                        'move_in_date'  => $assignment->actual_move_in,
                        'unit' => $unit ? [
                            'id'   => $unit->id,
                            'name' => $unit->name,
                        ] : null,
                        'room' => $room ? [
                            'id'          => $room->id,
                            'name'        => $room->name ?? 'Room ' . $room->room_number,
                            'room_number' => $room->room_number,
                        ] : null,
                        'bed' => $bed ? [
                            'id'         => $bed->id,
                            'bed_number' => $bed->bed_number,
                            'bed_label'  => $bed->bed_label ?? 'Bed ' . $bed->bed_number,
                            'base_rent'  => $bed->base_rent,
                        ] : null,
                    ] : null,
                ];
            });

            return $this->success($data, 'Leases fetched successfully', 200);
        } catch (Exception $e) {
            Log::error('myLeases error: ' . $e->getMessage());
            return $this->error([], 'Failed to load leases', 500);
        }
    }

    /**
     * Available units in the property for lease
     * GET /api/maintanance/lease/{leaseId}/units
     */
    public function getUnits(Request $request, $leaseId)
    {
        try {
            $tenant = auth('api')->user();

            // Verify that the lease belongs to this tenant.
            $lease = Lease::where('tenant_id', $tenant->id)
                ->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
                ->findOrFail($leaseId);

            $units = Unit::where('property_id', $lease->property_id)
                ->where('is_active', true)
                ->select('id', 'name', 'gender_designation')
                ->get();

            return $this->success($units, 'Units fetched successfully', 200);
        } catch (Exception $e) {
            Log::error('getUnits error: ' . $e->getMessage());
            return $this->error([], 'Failed to load units', 500);
        }
    }

    /**
     * Unit এর available rooms
     * GET /api/maintanance/unit/{unitId}/rooms
     */
    public function getRooms(Request $request, $unitId)
    {
        try {
            $tenant = auth('api')->user();

            // Verify if this unit is on the tenant's lease.
            $hasAccess = Lease::where('tenant_id', $tenant->id)
                ->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
                ->whereHas('currentAssignment.bed.room.unit', fn($q) => $q->where('id', $unitId))
                ->orWhereHas('property.units', fn($q) => $q->where('id', $unitId))
                ->exists();

            // Give rooms only if the unit exists (strict check optional)
            $rooms = Room::where('unit_id', $unitId)
                ->where('is_active', true)
                ->select('id', 'room_number', 'name', 'gender_designation')
                ->get()
                ->map(fn($r) => [
                    'id'          => $r->id,
                    'name'        => $r->name ?? 'Room ' . $r->room_number,
                    'room_number' => $r->room_number,
                ]);

            return $this->success($rooms, 'Rooms fetched successfully', 200);
        } catch (Exception $e) {
            Log::error('getRooms error: ' . $e->getMessage());
            return $this->error([], 'Failed to load rooms', 500);
        }
    }

    /**
     * Room's available beds
     * GET /api/maintanance/room/{roomId}/beds
     */
    public function getBeds(Request $request, $roomId)
    {
        try {
            $beds = Bed::where('room_id', $roomId)
                ->select('id', 'bed_number', 'bed_label', 'base_rent', 'is_occupied')
                ->get()
                ->map(fn($b) => [
                    'id'          => $b->id,
                    'bed_number'  => $b->bed_number,
                    'bed_label'   => $b->bed_label ?? 'Bed ' . $b->bed_number,
                    'base_rent'   => $b->base_rent,
                    'is_occupied' => (bool) $b->is_occupied,
                ]);

            return $this->success($beds, 'Beds fetched successfully', 200);
        } catch (Exception $e) {
            Log::error('getBeds error: ' . $e->getMessage());
            return $this->error([], 'Failed to load beds', 500);
        }
    }

    /*==========================================================
    | MAINTENANCE CRUD
    ==========================================================*/

    /**
     * List tenant's maintenance requests
     * GET /api/maintanance/list
     */
    public function index(Request $request)
    {
        try {
            $tenant  = auth('api')->user();
            $perPage = $request->get('per_page', 10);

            // Status filter
            $query = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with([
                    'property:id,name,address',
                    'unitModel:id,name',
                    'room:id,room_number,name',
                    'bed:id,bed_number,bed_label,base_rent',
                    'attachments',
                    'tenant.activeLease.property',
                    'tenant.activeLease.currentAssignment.bed.room.unit',
                ])
                ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            $requests = $query->paginate($perPage);

            return $this->success(
                MaintenanceRequestResource::collection($requests)->response()->getData(true),
                'Maintenance requests fetched successfully',
                200
            );
        } catch (Exception $e) {
            Log::error('Maintenance list error: ' . $e->getMessage());
            return $this->error([], 'Failed to load maintenance requests', 500);
        }
    }

    /**
     * Create new maintenance request
     * POST /api/maintanance/store
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lease_id'         => 'nullable|exists:leases,id',
            'property_id'      => 'nullable|exists:properties,id',
            'unit_id'          => 'nullable|exists:units,id',
            'room_id'          => 'nullable|exists:rooms,id',
            'bed_id'           => 'nullable|exists:beds,id',
            'title'            => 'required|string|max:255',
            'category'         => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'      => 'nullable|string',
            'is_urgent'        => 'nullable|boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments'      => 'nullable|array',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,mp4,mov|max:20480',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            // Property/Unit/Room/Bed resolve logic
            $propertyId = $request->property_id;
            $unitId     = $request->unit_id;
            $roomId     = $request->room_id;
            $bedId      = $request->bed_id;

            // if lease_id is provided, try to auto-fill property/unit/room/bed based on the lease assignment
            if ($request->filled('lease_id') && !$propertyId) {
                $lease = Lease::where('tenant_id', $tenant->id)
                    ->with('currentAssignment.bed.room.unit')
                    ->find($request->lease_id);

                if ($lease) {
                    $propertyId = $propertyId ?? $lease->property_id;
                    $bed        = $lease->currentAssignment?->bed;
                    $unitId     = $unitId  ?? $bed?->room?->unit?->id;
                    $roomId     = $roomId  ?? $bed?->room?->id;
                    $bedId      = $bedId   ?? $bed?->id;
                }
            }

            // If all are null, auto-fill from active lease.
            if (!$propertyId) {
                $activeLease = Lease::where('tenant_id', $tenant->id)
                    ->where('status', 'ACTIVE')
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
                'tenant_id'        => $tenant->id,
                'property_id'      => $propertyId,
                'unit_id'          => $unitId,
                'room_id'          => $roomId,
                'bed_id'           => $bedId,
                'title'            => $request->title,
                'category'         => $request->category,
                'description'      => $request->description,
                'is_urgent'        => $request->boolean('is_urgent'),
                'grant_permission' => $request->boolean('grant_permission'),
                'status'           => 'pending',
            ]);

            // Attachments
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

            $maintenance->load([
                'property:id,name,address',
                'unitModel:id,name',
                'room:id,room_number,name',
                'bed:id,bed_number,bed_label,base_rent',
                'attachments',
                'tenant.activeLease',
            ]);

            return $this->success(
                new MaintenanceRequestResource($maintenance),
                'Maintenance request created successfully',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Maintenance store error: ' . $e->getMessage());
            return $this->error([], 'Failed to create maintenance request', 500);
        }
    }

    /**
     * Get single maintenance request
     * GET /api/maintanance/edit/{id}
     */
    public function edit($maintananceId)
    {
        try {
            $tenant = auth('api')->user();

            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with([
                    'property:id,name,address',
                    'unitModel:id,name',
                    'room:id,room_number,name',
                    'bed:id,bed_number,bed_label,base_rent',
                    'attachments',
                    'tenant.activeLease.property',
                    'tenant.activeLease.currentAssignment.bed.room.unit',
                ])
                ->findOrFail($maintananceId);

            return $this->success(
                new MaintenanceRequestResource($maintenance),
                'Maintenance request loaded',
                200
            );
        } catch (Exception $e) {
            Log::error('Maintenance edit error: ' . $e->getMessage());
            return $this->error([], 'Maintenance request not found', 404);
        }
    }

    /**
     * Update maintenance request
     * POST /api/maintanance/update/{id}
     */
    public function update(Request $request, $maintenanceId)
    {
        $validator = Validator::make($request->all(), [
            'property_id'      => 'nullable|exists:properties,id',
            'unit_id'          => 'nullable|exists:units,id',
            'room_id'          => 'nullable|exists:rooms,id',
            'bed_id'           => 'nullable|exists:beds,id',
            'title'            => 'nullable|string|max:255',
            'category'         => 'nullable|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'      => 'nullable|string',
            'is_urgent'        => 'nullable|boolean',
            'grant_permission' => 'nullable|boolean',
            'status'           => 'nullable|in:pending,cancelled',
            'attachments'      => 'nullable|array',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,mp4,mov|max:20480',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->findOrFail($maintenanceId);

            // Completed/Rejected request tenant cannot update
            if (in_array($maintenance->status, ['completed', 'rejected'])) {
                return $this->error(
                    [],
                    'Cannot update a ' . $maintenance->status . ' request.',
                    403
                );
            }

            // Update fields (keep old value if null)
            $maintenance->update(array_filter([
                'property_id'      => $request->property_id,
                'unit_id'          => $request->unit_id,
                'room_id'          => $request->room_id,
                'bed_id'           => $request->bed_id,
                'title'            => $request->title,
                'category'         => $request->category,
                'description'      => $request->description,
                'is_urgent'        => $request->has('is_urgent') ? $request->boolean('is_urgent') : null,
                'grant_permission' => $request->has('grant_permission') ? $request->boolean('grant_permission') : null,
                'status'           => $request->status,
            ], fn($v) => !is_null($v)));

            // New attachments
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                if (!is_array($files)) $files = [$files];

                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $path = Helper::uploadImage($file, 'maintenance');
                        MaintenanceRequestAttachment::create([
                            'maintenance_request_id' => $maintenance->id,
                            'attachment_path'        => $path,
                        ]);
                    }
                }
            }

            DB::commit();

            $maintenance->load([
                'property:id,name,address',
                'unitModel:id,name',
                'room:id,room_number,name',
                'bed:id,bed_number,bed_label,base_rent',
                'attachments',
                'tenant.activeLease',
            ]);

            return $this->success(
                new MaintenanceRequestResource($maintenance),
                'Maintenance request updated successfully',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Maintenance update error: ' . $e->getMessage());
            return $this->error([], 'Failed to update maintenance request', 500);
        }
    }

    /**
     * Delete maintenance request
     * DELETE /api/maintanance/delete/{id}
     */
    public function destroy($maintenanceId)
    {
        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('attachments')
                ->find($maintenanceId);

            if (!$maintenance) {
                return $this->error([], 'Maintenance request not found!', 404);
            }

            // Completed request cannot be deleted.
            if ($maintenance->status === 'completed') {
                return $this->error([], 'Cannot delete a completed request.', 403);
            }

            // File delete
            foreach ($maintenance->attachments as $attachment) {
                Helper::deleteImage($attachment->attachment_path);
            }

            $maintenance->delete();

            DB::commit();

            return $this->success([], 'Maintenance request deleted successfully', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Maintenance delete error: ' . $e->getMessage());
            return $this->error([], 'Failed to delete maintenance request', 500);
        }
    }
}
