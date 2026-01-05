<?php

namespace App\Http\Controllers\Api\Tenents;

use Exception;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\TenantAddress;
use App\Models\TenantProfile;
use App\Models\TenantDocument;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\TenantEmergencyContact;
use App\Models\TenantEmploymentHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Tenant\TenentApplicationRequest;

class TenantFormController extends Controller
{

    /**
     * Show tenant application form
     */
    public function show($token)
    {
        $tenantId = Cache::get("tenant_form_token_{$token}");

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired form access token.'
            ], 403);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.'
            ], 404);
        }

        if ($tenant->profile) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted your application.',
                'data' => [
                    'status' => $tenant->status
                ]
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Form access granted',
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Submit application form
     */
    public function submit(TenentApplicationRequest $request, $token)
    {
        $tenantId = Cache::get("tenant_form_token_{$token}");

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired form access token.'
            ], 403);
        }

        $validator = Validator::validated();

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $tenant = Tenant::findOrFail($tenantId);

            // Update tenant
            $tenant->update([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'move_in_date' => $request->move_in_date,
                'arrival_date' => $request->arrival_date,
                'status' => 'approved', // Changed to approved after form submission
            ]);

            // Create profile
            $profile = TenantProfile::create([
                'tenant_id' => $tenant->id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'country_code' => $request->country_code,
            ]);

            // Create address
            $address = TenantAddress::create([
                'tenant_id' => $tenant->id,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'country' => $request->country,
            ]);

            // Create employment history
            $employment = TenantEmploymentHistory::create([
                'tenant_id' => $tenant->id,
                'employment_status' => $request->employment_status,
                'employer' => $request->employer,
                'title' => $request->title,
                'contact_person_name' => $request->contact_person_name,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'is_current_working' => $request->is_current_working ?? false,
                'school' => $request->school,
                'start_date' => $request->start_date,
                'graduation_date' => $request->graduation_date,
            ]);

            // Create emergency contact
            $emergencyContact = TenantEmergencyContact::create([
                'tenant_id' => $tenant->id,
                'name' => $request->emergency_name,
                'phone' => $request->emergency_phone,
                'email' => $request->emergency_email,
                'relationship' => $request->emergency_relationship,
            ]);

            // Handle document uploads
            $documentTypes = ['passport', 'visa', 'id_front', 'id_back'];
            $uploadedDocuments = [];

            foreach ($documentTypes as $docType) {
                if ($request->hasFile($docType)) {
                    $file = $request->file($docType);
                    $path = $file->store("tenant_documents/{$tenant->id}", 'private');

                    $document = TenantDocument::create([
                        'tenant_id' => $tenant->id,
                        'document_type' => $docType,
                        'file_path' => $path,
                        'file_original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $uploadedDocuments[] = $document;
                }
            }

            // Handle other documents
            if ($request->hasFile('other')) {
                foreach ($request->file('other') as $file) {
                    $path = $file->store("tenant_documents/{$tenant->id}", 'private');

                    $document = TenantDocument::create([
                        'tenant_id' => $tenant->id,
                        'document_type' => 'other',
                        'file_path' => $path,
                        'file_original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $uploadedDocuments[] = $document;
                }
            }

            // Invalidate token
            Cache::forget("tenant_form_token_{$token}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Your application has been submitted successfully. We will review it and get back to you soon.',
                'data' => [
                    'tenant' => [
                        'id' => $tenant->id,
                        'email' => $tenant->email,
                        'status' => $tenant->status,
                        'profile' => $profile,
                        'address' => $address,
                        'employment' => $employment,
                        'emergency_contact' => $emergencyContact,
                        'documents_count' => count($uploadedDocuments)
                    ]
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
