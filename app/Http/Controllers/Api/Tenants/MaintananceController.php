<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MaintananceController extends Controller
{
    /**
     * List tenant maintenance requests
     */
    public function index(Request $request)
    {
        try {
            $tenant = auth('api')->user();

            $perPage = $request->get('per_page', 10);

            $requests = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('attachments')
                ->latest()
                ->paginate($perPage);

            return $this->success($requests, 'Maintenance requests fetched successfully', 200);
        } catch (\Exception $e) {
            Log::error('Maintenance list error: ' . $e->getMessage());
            return $this->error([], 'Failed to load maintenance requests', 500);
        }
    }

    /**
     * Store new maintenance request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id'      => 'required|exists:properties,id',
            'unit'             => 'nullable|string|max:50',
            'title'            => 'required|string|max:255',
            'category'         => 'required|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'      => 'nullable|string',
            'is_urgent'        => 'nullable|boolean',
            'grant_permission' => 'nullable|boolean',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:20480',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            $requestData = $request->only([
                'property_id',
                'unit',
                'title',
                'category',
                'description',
                'is_urgent',
                'grant_permission'
            ]);

            $requestData['tenant_id'] = $tenant->id;
            $requestData['status'] = 'pending';

            $maintenance = MaintenanceRequest::create($requestData);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                $files = [];

                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('maintenance', 'public');
                    $files[] = $path;
                }

                MaintenanceRequestAttachment::create([
                    'maintenance_request_id' => $maintenance->id,
                    'attachments' => $files
                ]);
            }

            DB::commit();

            return $this->success(
                $maintenance->load('attachments'),
                'Maintenance request created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Maintenance store error: ' . $e->getMessage());
            return $this->error([], 'Failed to create maintenance request', 500);
        }
    }

    /**
     * Edit single maintenance request
     */
    public function edit($maintananceId)
    {
        try {
            $tenant = auth('api')->user();

            $request = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('attachments')
                ->findOrFail($maintananceId);

            return $this->success($request, 'Maintenance request loaded', 200);
        } catch (\Exception $e) {
            Log::error('Maintenance edit error: ' . $e->getMessage());
            return $this->error([], 'Maintenance request not found', 404);
        }
    }

    /**
     * Update maintenance request
     */
    public function update(Request $request, $maintananceId)
    {
        $validator = Validator::make($request->all(), [
            'unit'             => 'nullable|string|max:50',
            'title'            => 'nullable|string|max:255',
            'category'         => 'nullable|in:ac,appliance,electrical,heat,kitchen,plumbing,other',
            'description'      => 'nullable|string',
            'is_urgent'        => 'nullable|boolean',
            'grant_permission' => 'nullable|boolean',
            'status'           => 'nullable|in:pending,in_progress,completed,rejected,cancelled',
            'attachments.*'    => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:20480',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->findOrFail($maintananceId);

            $maintenance->update($request->only([
                'unit',
                'title',
                'category',
                'description',
                'is_urgent',
                'grant_permission',
                'status'
            ]));

            // New attachments
            if ($request->hasFile('attachments')) {
                $files = [];

                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('maintenance', 'public');
                    $files[] = $path;
                }

                MaintenanceRequestAttachment::create([
                    'maintenance_request_id' => $maintenance->id,
                    'attachments' => $files
                ]);
            }

            DB::commit();

            return $this->success(
                $maintenance->load('attachments'),
                'Maintenance request updated successfully',
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Maintenance update error: ' . $e->getMessage());
            return $this->error([], 'Failed to update maintenance request', 500);
        }
    }

    /**
     * Delete maintenance request
     */
    public function destroy($maintananceId)
    {
        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->findOrFail($maintananceId);

            $maintenance->delete();

            DB::commit();

            return $this->success([], 'Maintenance request deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Maintenance delete error: ' . $e->getMessage());
            return $this->error([], 'Failed to delete maintenance request', 500);
        }
    }
}
