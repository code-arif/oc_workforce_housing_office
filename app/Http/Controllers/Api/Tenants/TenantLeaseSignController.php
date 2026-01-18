<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Traits\ApiResponse;
use App\Services\Tenants\LeaseSigningService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TenantLeaseSignController extends Controller
{
    use ApiResponse;

    protected $signingService;

    public function __construct(LeaseSigningService $signingService)
    {
        $this->signingService = $signingService;
    }

    /**
     * Get lease document for signing
     */
    public function getLeaseDocument(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);

            if (!$document) {
                return $this->error([], 'Lease document not found or unauthorized', 404);
            }

            return $this->success([
                'document' => $document
            ], 'Lease document retrieved successfully');

        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Sign lease document
     */
    public function signLease(Request $request, $leaseId)
    {
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string', // Base64 signature or digital signature
            'signature_type' => 'nullable|in:digital,electronic,wet',
            'ip_address' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            $result = $this->signingService->signLease(
                $leaseId,
                $tenant->id,
                $request->signature,
                $request->signature_type ?? 'digital',
                $request->ip_address ?? $request->ip()
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'lease' => $result['lease'],
                'document' => $result['document']
            ], 'Lease signed successfully');

        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Check if lease can be signed
     */
    public function checkSigningEligibility(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $eligibility = $this->signingService->checkSigningEligibility($leaseId, $tenant->id);

            return $this->success([
                'can_sign' => $eligibility['can_sign'],
                'reason' => $eligibility['reason'] ?? null,
                'lease_status' => $eligibility['lease_status'],
                'document_status' => $eligibility['document_status'] ?? null,
            ], 'Eligibility checked successfully');

        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get lease document PDF for preview
     */
    public function previewDocument(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $documentPath = $this->signingService->getDocumentPath($leaseId, $tenant->id);

            if (!$documentPath) {
                return $this->error([], 'Document not found', 404);
            }

            return $this->success([
                'document_url' => $documentPath
            ], 'Document path retrieved successfully');

        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
