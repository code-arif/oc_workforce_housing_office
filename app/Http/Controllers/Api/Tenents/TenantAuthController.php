<?php

namespace App\Http\Controllers\Api\Tenents;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TenantAuthController extends Controller
{
    use ApiResponse;

    /**
     * Tenant login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        $tenant = Tenant::where('email', $request->email)->first();

        if (!$tenant || !Hash::check($request->password, $tenant->password)) {
            return $this->error([], 'Invalid credentials', 401);
        }

        if ($tenant->status !== 'active') {
            return $this->error(
                ['status' => $tenant->status],
                'Your account is not active. Please contact administrator.',
                403
            );
        }

        $token = $tenant->createToken('tenant-token')->plainTextToken;

        return $this->success(
            [
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                    'profile' => $tenant->profile,
                ],
                'token' => $token . 'Only of testing !!!',
                'token_type' => 'Bearer'
            ],
            'Login successful',
            200
        );
    }

    /**
     * Tenant Profile
     */
    public function profile(Request $request)
    {
        $tenant = $request->user();

        $tenant->load([
            'profile',
            'address',
            'employmentHistories',
            'emergencyContacts',
            'documents'
        ]);

        return $this->success(['tenant' => $tenant], 'Tenant profile data fatched successfully!', 200);
    }

    /**
     * Update tenant profile
     */
    public function updateProfile(Request $request)
    {
        $tenant = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'country_code' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($tenant->profile) {
                $tenant->profile->update($request->only([
                    'first_name',
                    'middle_name',
                    'last_name',
                    'phone',
                    'country_code'
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'profile' => $tenant->profile
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User login
     */
    public function documents(Request $request)
    {
        $tenant = $request->user();
        $documents = $tenant->documents;

        return response()->json([
            'success' => true,
            'data' => [
                'documents' => $documents
            ]
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $tenant = $request->user();

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:passport,visa,id_front,id_back,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $path = $file->store("tenant_documents/{$tenant->id}", 'private');

            $document = TenantDocument::create([
                'tenant_id' => $tenant->id,
                'document_type' => $request->document_type,
                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'document' => $document
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}
