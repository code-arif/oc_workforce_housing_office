<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
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

    /**
     * List tenant maintenance requests
     */
    public function index(Request $request)
    {
        try {
            $tenant = auth('api')->user();

            $perPage = $request->get('per_page', 10);

            // $requests = MaintenanceRequest::where('tenant_id', $tenant->id)
            //     ->with('attachments')
            //     ->latest()
            //     ->paginate($perPage);

            // return $this->success($requests, 'Maintenance requests fetched successfully', 200);
            $requests = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('attachments')
                ->latest()
                ->paginate($perPage);

            $requests->getCollection()->transform(function ($request) {
                $request->attachments->map(function ($attachment) {
                    $attachment->attachment_path = asset($attachment->attachment_path);
                    return $attachment;
                });
                return $request;
            });

            return $this->success($requests, 'Maintenance requests fetched successfully', 200);
        } catch (Exception $e) {
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
            'property_id'      => 'nullable|exists:properties,id',
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
                $files = $request->file('attachments');

                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $path = Helper::uploadImage($file, 'maintenance');
                        MaintenanceRequestAttachment::create([
                            'maintenance_request_id' => $maintenance->id,
                            'attachment_path' => $path
                        ]);
                    }
                }
            }

            DB::commit();

            return $this->success(
                new MaintenanceRequestResource($maintenance->load('attachments')),
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
     * Edit single maintenance request
     */
    public function edit($maintananceId)
    {
        try {
            $tenant = auth('api')->user();

            $request = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('attachments')
                ->findOrFail($maintananceId);

            return $this->success(
                new MaintenanceRequestResource($request->load('attachments')),
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
     */
    public function update(Request $request, $maintenanceId)
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

            // Find the maintenance request owned by this tenant
            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->findOrFail($maintenanceId);

            // Update only the fields that are provided
            $maintenance->update($request->only([
                'unit',
                'title',
                'category',
                'description',
                'is_urgent',
                'grant_permission',
                'status'
            ]));

            // Handle NEW attachments (add only — existing ones remain untouched)
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');

                // Normalize to array if single file
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $path = Helper::uploadImage($file, 'maintenance'); // same as store

                        MaintenanceRequestAttachment::create([
                            'maintenance_request_id' => $maintenance->id,
                            'attachment_path'        => $path,
                        ]);
                    }
                }
            }

            DB::commit();

            // Return clean response using Resource (same as store)
            return $this->success(
                new MaintenanceRequestResource($maintenance->load('attachments')),
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
     */
    public function destroy($maintenanceId)
    {
        try {
            DB::beginTransaction();

            $tenant = auth('api')->user();

            // withTrashed() will also find soft deleted if it has already been deleted
            $maintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('attachments')
                ->find($maintenanceId);

            if (!$maintenance) {
                return $this->error([], 'Maintenance request not found!', 404);
            }

            // Physically delete the files
            foreach ($maintenance->attachments as $attachment) {
                Helper::deleteImage($attachment->attachment_path);
            }

            $maintenance->delete();           // soft delete
            // $maintenance->forceDelete();   // permanent delete

            DB::commit();

            return $this->success([], 'Maintenance request deleted successfully', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Maintenance delete error: ' . $e->getMessage());
            return $this->error([], 'Failed to delete maintenance request', 500);
        }
    }
}
