<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_source' => $this->application_source,
            'status' => $this->status,
            'move_in_date' => $this->move_in_date?->format('Y-m-d'),
            'arrival_date' => $this->arrival_date?->format('Y-m-d'),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'email' => $this->email,

            'profile' => new TenantProfileResource($this->whenLoaded('profile')),
            'address' => new TenantAddressResource($this->whenLoaded('address')),

            'employments' => TenantEmploymentResource::collection(
                $this->whenLoaded('employments')
            ),

            'emergency_contacts' => TenantEmergencyContactResource::collection(
                $this->whenLoaded('emergencyContacts')
            ),

            'documents' => TenantDocumentResource::collection(
                $this->whenLoaded('documents')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
