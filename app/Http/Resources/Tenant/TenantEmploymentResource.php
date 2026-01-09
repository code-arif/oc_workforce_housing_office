<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantEmploymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'employment_status' => $this->employment_status,
            'employer' => $this->employer,
            'title' => $this->title,
            'contact_person_name' => $this->contact_person_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'is_current_working' => (bool) $this->is_current_working,
            'school' => $this->school,
            'start_date' => $this->start_date,
            'graduation_date' => $this->graduation_date,
        ];
    }
}
