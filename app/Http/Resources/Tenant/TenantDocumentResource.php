<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'document_type' => $this->document_type,
            'file_name' => $this->file_original_name,
            'path' => $this->file_path,
            'mime_type' => $this->mime_type,
        ];
    }
}
