<?php

namespace App\Http\Resources\Maintanace;

use App\Helper\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'category'         => $this->category,
            'description'      => $this->description,
            'is_urgent'        => (bool) $this->is_urgent,
            'grant_permission' => (bool) $this->grant_permission,
            'status'           => $this->status,
            'tenant_id'        => $this->tenant_id,
            'property_id'      => $this->property_id,
            'unit'             => $this->unit,
            'created_at'       => $this->created_at->toDateTimeString(),
            'updated_at'       => $this->updated_at->toDateTimeString(),

            // Attachments - Collection
            'attachments'      => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id'                     => $attachment->id,
                        // 'attachment_path'        => $attachment->attachment_path,
                        'attachment_path' => FileUrl::resolve($this->attachment_path, 'public') ?? asset('default/placeholder-image.avif'),
                        'created_at'             => $attachment->created_at->toDateTimeString(),
                        'updated_at'             => $attachment->updated_at->toDateTimeString(),
                    ];
                });
            }),
        ];
    }
}
