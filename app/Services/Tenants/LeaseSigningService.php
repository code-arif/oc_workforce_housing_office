<?php

namespace App\Services\Tenants;

use Exception;
use App\Models\Lease;
use App\Helper\FileUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Lease\LeaseDocument;
use Illuminate\Support\Facades\Storage;
use App\Mail\Tenant\LeaseFullySignedMail;

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

            // Update document with signature
            $document->update([
                'tenant_signed_at' => now(),
                'tenant_signature' => $signature,
                'tenant_signature_type' => $signatureType,
            ]);

            // Update lease status
            if ($lease->status === 'PENDING_TENANT_SIGN') {
                $lease->status = 'PENDING_ADMIN_SIGN';
            } elseif ($lease->status == "PENDING_ADMIN_SIGN") {
                $lease->status = "ACTIVE";
                $document->status = "signed";
                $document->save();
            }else {
                // This should not happen due to earlier check, but just in case
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Invalid lease status for signing'
                ];
            }
            $lease->update([
                'status' => $lease->status
            ]);

            $fullySignedNow = $lease->status === 'ACTIVE' && $document->admin_signed_at;

            DB::commit();

            // Send notification when both parties have signed
            if ($fullySignedNow) {
                try {
                    $lease->load(['tenant.profile', 'property', 'assignments.bed']);
                    Mail::to($lease->tenant->email)->queue(new LeaseFullySignedMail($lease));
                } catch (Exception $e) {
                    Log::error('Failed to send lease-fully-signed email: ' . $e->getMessage());
                }
            }

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
