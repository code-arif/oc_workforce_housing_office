<?php

namespace App\Http\Resources\Lease;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\FileUrl;

class LeaseDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $template = $this->leaseTemplate ?? $this->template;
        $pdfPath = $template?->pdf_path ?? $template?->document_path ?? null;

        // Get placeholders and signatures from template
        $placeholders = [];
        $signatures = [];

        if ($template) {
            $placeholders = is_array($template->placeholders)
                ? $template->placeholders
                : (json_decode($template->placeholders, true) ?? []);
            $signatures = is_array($template->signatures)
                ? $template->signatures
                : (json_decode($template->signatures, true) ?? []);
        }

        return [
            'id' => $this->id,
            'lease_id' => $this->lease_id,

            'lease' => new LeaseResource($this->lease),

            'template' => [
                'id' => $template?->id,
                'name' => $template?->name ?? 'Lease Agreement',
                'pdf_url' => $pdfPath ? asset('storage/' . $pdfPath) : null,
                'total_pages' => $template?->total_pages ?? 1,
                'placeholders' => $placeholders,
                'signatures' => $signatures,
            ],

            'rendered_content' => FileUrl::resolve(
                $this->rendered_content,
                'public'
            ),

            'tenant_signed_at' => $this->tenant_signed_at?->toISOString(),
            'admin_signed_at' => $this->admin_signed_at?->toISOString(),

            'tenant_signature' => $this->tenant_signature,
            'admin_signature' => $this->admin_signature,

            'status' => $this->status,

            'is_fully_signed' => $this->tenant_signed_at && $this->admin_signed_at,
            'is_tenant_signed' => (bool) $this->tenant_signed_at,
            'is_admin_signed' => (bool) $this->admin_signed_at,

            'can_sign' =>
            !$this->tenant_signed_at &&
                $this->lease?->status === 'PENDING_TENANT_SIGN',

            'download_url' => route('lease.signing.download', [
                'leaseId' => $this->lease_id
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
