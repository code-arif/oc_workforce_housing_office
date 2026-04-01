<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Models\Lease;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Lease\LeaseDocument;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LeaseDocumentController extends Controller
{
    /**
     * Get lease document for signing
     * 
     * @param int $documentId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocument($documentId, Request $request)
    {
        try {
            // Validate and decode the token
            $tokenData = $this->validateSignatureToken($request->token);
            
            if (!$tokenData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired signature token.'
                ], 401);
            }

            // Verify document ID matches token
            if ($tokenData['document_id'] != $documentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document ID mismatch.'
                ], 403);
            }

            // Get the lease document with relationships
            $leaseDocument = LeaseDocument::with([
                'lease.property',
                'lease.tenant.profile',
                'leaseTemplate'
            ])->find($documentId);

            if (!$leaseDocument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lease document not found.'
                ], 404);
            }

            // Check if document is already signed by tenant
            if ($leaseDocument->tenant_signed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This document has already been signed.',
                    'already_signed' => true,
                    'signed_at' => $leaseDocument->tenant_signed_at->format('F d, Y \a\t h:i A')
                ], 400);
            }

            $lease = $leaseDocument->lease;
            $tenant = $lease->tenant;
            $property = $lease->property;

            return response()->json([
                'success' => true,
                'data' => [
                    'document' => [
                        'id' => $leaseDocument->id,
                        'content' => $leaseDocument->rendered_content,
                        'status' => $leaseDocument->status,
                        'created_at' => $leaseDocument->created_at->format('F d, Y'),
                    ],
                    'lease' => [
                        'id' => $lease->id,
                        'start_date' => $lease->start_date,
                        'end_date' => $lease->end_date,
                        'rent_amount' => $lease->rent_amount,
                        'deposit_amount' => $lease->deposit_amount,
                        'payment_frequency' => $lease->payment_frequency,
                    ],
                    'tenant' => [
                        'id' => $tenant->id,
                        'email' => $tenant->email,
                        'name' => $tenant->profile ? 
                            trim($tenant->profile->first_name . ' ' . $tenant->profile->last_name) : 
                            'Tenant',
                    ],
                    'property' => [
                        'id' => $property->id ?? null,
                        'name' => $property->name ?? 'N/A',
                        'address' => $property ? 
                            $property->address . ', ' . $property->city . ', ' . $property->state . ' ' . $property->zip_code : 
                            'N/A',
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching lease document: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve lease document.'
            ], 500);
        }
    }

    /**
     * Sign the lease document
     * 
     * @param int $documentId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signDocument($documentId, Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'signature' => 'required|string', // Base64 encoded signature image or typed signature
                'signature_type' => 'required|in:drawn,typed', // Type of signature
                'agreed_to_terms' => 'required|boolean|accepted',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate and decode the token
            $tokenData = $this->validateSignatureToken($request->token);
            
            if (!$tokenData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired signature token.'
                ], 401);
            }

            // Verify document ID matches token
            if ($tokenData['document_id'] != $documentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document ID mismatch.'
                ], 403);
            }

            // Get the lease document
            $leaseDocument = LeaseDocument::with(['lease.tenant'])->find($documentId);

            if (!$leaseDocument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lease document not found.'
                ], 404);
            }

            // Check if already signed
            if ($leaseDocument->tenant_signed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This document has already been signed.',
                    'already_signed' => true
                ], 400);
            }

            // Get client IP and user agent for audit
            $clientIp = $request->ip();
            $userAgent = $request->userAgent();

            // Update the document with tenant signature
            $leaseDocument->update([
                'tenant_signature' => $request->signature,
                'tenant_signed_at' => now(),
                'status' => $leaseDocument->admin_signed_at ? 'signed' : 'pending_signatures',
            ]);

            // Create signature audit log if the model exists
            try {
                \App\Models\SignatureAuditLog::create([
                    'lease_document_id' => $leaseDocument->id,
                    'tenant_id' => $tokenData['tenant_id'],
                    'action' => 'tenant_signed',
                    'signature_type' => $request->signature_type,
                    'ip_address' => $clientIp,
                    'user_agent' => $userAgent,
                    'signed_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Audit log is optional, log error but don't fail
                Log::warning('Could not create signature audit log: ' . $e->getMessage());
            }

            // Update lease status if both parties have signed
            if ($leaseDocument->admin_signed_at && $leaseDocument->tenant_signed_at) {
                $leaseDocument->lease->update([
                    'status' => 'ACTIVE'
                ]);
            } else {
                // Update lease to pending admin signature
                $leaseDocument->lease->update([
                    'status' => 'PENDING_ADMIN_SIGN'
                ]);
            }

            Log::info('Lease document signed by tenant. Document ID: ' . $documentId . ', Tenant ID: ' . $tokenData['tenant_id']);

            return response()->json([
                'success' => true,
                'message' => 'Lease document signed successfully.',
                'data' => [
                    'document_id' => $leaseDocument->id,
                    'signed_at' => now()->format('F d, Y \a\t h:i A'),
                    'status' => $leaseDocument->admin_signed_at ? 'fully_signed' : 'pending_admin_signature',
                    'next_step' => $leaseDocument->admin_signed_at ? 
                        'Your lease is now fully executed. You will receive a copy shortly.' : 
                        'Your signature has been recorded. The property manager will review and countersign the document.',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error signing lease document: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sign lease document. Please try again.'
            ], 500);
        }
    }

    /**
     * Validate the signature token
     * 
     * @param string|null $token
     * @return array|null
     */
    protected function validateSignatureToken(?string $token): ?array
    {
        if (!$token) {
            return null;
        }

        try {
            $decoded = json_decode(base64_decode($token), true);
            
            if (!$decoded || !isset($decoded['document_id'], $decoded['tenant_id'], $decoded['lease_id'], $decoded['expires'])) {
                return null;
            }

            // Check if token has expired
            if ($decoded['expires'] < now()->timestamp) {
                return null;
            }

            // Verify the document and tenant exist
            $document = LeaseDocument::find($decoded['document_id']);
            $tenant = Tenant::find($decoded['tenant_id']);

            if (!$document || !$tenant) {
                return null;
            }

            // Verify the tenant is associated with this lease
            if ($document->lease->tenant_id != $decoded['tenant_id']) {
                return null;
            }

            return $decoded;

        } catch (\Exception $e) {
            Log::error('Token validation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download signed lease document as PDF
     * 
     * @param int $documentId
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadDocument($documentId, Request $request)
    {
        try {
            // For authenticated tenant
            $tenant = auth('api')->user();
            
            if (!$tenant) {
                // Try token-based access
                $tokenData = $this->validateSignatureToken($request->token);
                if (!$tokenData) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access.'
                    ], 401);
                }
                $tenantId = $tokenData['tenant_id'];
            } else {
                $tenantId = $tenant->id;
            }

            $leaseDocument = LeaseDocument::with(['lease'])->find($documentId);

            if (!$leaseDocument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.'
                ], 404);
            }

            // Verify tenant has access to this document
            if ($leaseDocument->lease->tenant_id != $tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this document.'
                ], 403);
            }

            // Generate PDF using mpdf
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
            ]);

            $mpdf->WriteHTML($leaseDocument->rendered_content);

            $filename = 'Lease_Agreement_' . $leaseDocument->lease->id . '_' . date('Y-m-d') . '.pdf';

            return response($mpdf->Output($filename, 'S'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading lease document: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document.'
            ], 500);
        }
    }
}
