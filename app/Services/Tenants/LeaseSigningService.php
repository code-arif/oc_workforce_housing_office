<?php

namespace App\Services\Tenants;

use Exception;
use App\Models\Lease;
use App\Helper\FileUrl;
use Illuminate\Support\Facades\DB;
use App\Models\Lease\LeaseDocument;
use Illuminate\Support\Facades\Storage;

class LeaseSigningService
{
    /**
     * Get lease document for signing
     */
    public function getLeaseDocument($leaseId, $tenantId)
    {
        $lease = Lease::with(['property', 'season'])
            ->where('id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$lease) {
            return null;
        }

        // $document = LeaseDocument::with('template')
        //     ->where('lease_id', $leaseId)
        //     ->where('tenant_id', $tenantId)
        //     ->first();

        $document = LeaseDocument::with(['template', 'lease.property'])
            ->where('lease_id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();


        if (!$document) {
            return null;
        }

        return $document;
    }

    /**
     * Sign lease document
     */
    public function signLease($leaseId, $tenantId, $signature, $signatureType, $ipAddress)
    {
        DB::beginTransaction();

        try {
            $lease = Lease::where('id', $leaseId)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$lease) {
                return [
                    'success' => false,
                    'message' => 'Lease not found or unauthorized'
                ];
            }

            // Check if lease can be signed
            if ($lease->status !== 'PENDING_TENANT_SIGN') {
                return [
                    'success' => false,
                    'message' => 'Lease is not pending tenant signature'
                ];
            }

            $document = LeaseDocument::where('lease_id', $leaseId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Lease document not found'
                ];
            }

            // Check if already signed
            if ($document->tenant_signed_at) {
                return [
                    'success' => false,
                    'message' => 'Lease has already been signed by tenant'
                ];
            }

            // Store signature (if it's base64, save to storage)
            $signaturePath = null;
            if ($signatureType === 'digital' && strpos($signature, 'data:image') === 0) {
                // Extract base64 data
                $image = str_replace('data:image/png;base64,', '', $signature);
                $image = str_replace(' ', '+', $image);
                $imageName = 'signatures/tenant_' . $tenantId . '_lease_' . $leaseId . '_' . time() . '.png';

                Storage::disk('public')->put($imageName, base64_decode($image));
                $signaturePath = $imageName;
            }

            // Update document with signature
            $document->update([
                'tenant_signed_at' => now(),
                'tenant_signature' => $signaturePath ?? $signature,
                'tenant_signature_type' => $signatureType,
                // 'tenant_signature_ip' => $ipAddress,
                // 'status' => 'pending_admin_signature',
            ]);

            // Update lease status
            $lease->update([
                'status' => 'PENDING_ADMIN_SIGN'
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Lease signed successfully',
                'lease' => [
                    'id' => $lease->id,
                    'status' => $lease->status,
                ],
                'document' => [
                    'id' => $document->id,
                    'tenant_signed_at' => $document->tenant_signed_at,
                    'status' => $document->status,
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if lease can be signed
     */
    public function checkSigningEligibility($leaseId, $tenantId)
    {
        $lease = Lease::where('id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$lease) {
            return [
                'can_sign' => false,
                'reason' => 'Lease not found',
                'lease_status' => null
            ];
        }

        $document = LeaseDocument::where('lease_id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$document) {
            return [
                'can_sign' => false,
                'reason' => 'Lease document not found',
                'lease_status' => $lease->status
            ];
        }

        if ($document->tenant_signed_at) {
            return [
                'can_sign' => false,
                'reason' => 'Lease already signed',
                'lease_status' => $lease->status,
                'document_status' => $document->status
            ];
        }

        if ($lease->status !== 'PENDING_TENANT_SIGN') {
            return [
                'can_sign' => false,
                'reason' => 'Lease is not pending tenant signature',
                'lease_status' => $lease->status,
                'document_status' => $document->status
            ];
        }

        return [
            'can_sign' => true,
            'lease_status' => $lease->status,
            'document_status' => $document->status
        ];
    }

    /**
     * Get document path for preview
     */
    public function getDocumentPath($leaseId, $tenantId)
    {
        $document = LeaseDocument::where('lease_id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$document) {
            return null;
        }

        $rendered_content = FileUrl::resolve(
            $document->rendered_content,
            'public'
        );

        return $rendered_content;
    }
}
