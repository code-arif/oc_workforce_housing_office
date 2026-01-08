<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use App\Models\TenantDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TenantDashboardController extends Controller
{
    use ApiResponse;
    
    /**
     * Tenant Dashboard Stats and data
     */
    public function dashboard(Request $request)
    {
        $tenant = $request->user();

        $tenant->load('profile');

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                    'move_in_date' => $tenant->move_in_date,
                    'profile' => $tenant->profile,
                ],
                'statistics' => [
                    'documents_count' => $tenant->documents()->count(),
                    'emergency_contacts_count' => $tenant->emergencyContacts()->count(),
                ]
            ]
        ]);
    }

    /**
     * Tenant documents
     */
    public function documents(Request $request)
    {
        $tenant = $request->user();
        $documents = $tenant->documents;

        return $this->success([
            'documents' => $documents
        ], 'Tenant documents', 200);
    }


    /**
     * Documents upload
     */
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
