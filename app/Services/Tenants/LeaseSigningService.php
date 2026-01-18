<?php

namespace App\Services\Tenants;

use App\Models\Lease;
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

        $document = LeaseDocument::with('template')
            ->where('lease_id', $leaseId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$document) {
            return null;
        }

        return [
            'id' => $document->id,
            'lease' => [
                'id' => $lease->id,
                'status' => $lease->status,
                'start_date' => $lease->start_date,
                'end_date' => $lease->end_date,
                'rent_amount' => $lease->rent_amount,
                'deposit_amount' => $lease->deposit_amount,
                'property' => $lease->property,
            ],
            'template_name' => $document->template->name ?? 'Lease Agreement',
            'rendered_content' => $document->rendered_content,
            'tenant_signed_at' => $document->tenant_signed_at,
            'admin_signed_at' => $document->admin_signed_at,
            'tenant_signature' => $document->tenant_signature,
            'admin_signature' => $document->admin_signature,
            'status' => $document->status,
            'can_sign' => !$document->tenant_signed_at && $lease->status === 'PENDING_TENANT_SIGN',
        ];
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

                Storage::disk('private')->put($imageName, base64_decode($image));
                $signaturePath = $imageName;
            }

            // Update document with signature
            $document->update([
                'tenant_signed_at' => now(),
                'tenant_signature' => $signaturePath ?? $signature,
                'tenant_signature_type' => $signatureType,
                'tenant_signature_ip' => $ipAddress,
                'status' => 'pending_admin_signature',
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
        } catch (\Exception $e) {
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

        // Return the rendered content or document path
        // This could be a URL to view the PDF or the content itself
        return $document->rendered_content;
    }
}
