<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantEmergencyContactResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'relationship' => $this->relationship,
        ];
    }
}
