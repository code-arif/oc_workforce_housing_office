<?php

namespace App\Http\Resources\Lease;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\FileUrl;

class LeaseDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'lease' => new LeaseResource($this->lease),

            'template_name' => $this->template->name ?? 'Lease Agreement',

            'rendered_content' => FileUrl::resolve(
                $this->rendered_content,
                'public'
            ),

            'tenant_signed_at' => $this->tenant_signed_at,
            'admin_signed_at' => $this->admin_signed_at,

            'tenant_signature' => $this->tenant_signature,
            'admin_signature' => $this->admin_signature,

            'status' => $this->status,

            'can_sign' =>
            !$this->tenant_signed_at &&
                $this->lease?->status === 'PENDING_TENANT_SIGN',
        ];
    }
}
