<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'avatar' => $this->avatar ? asset($this->avatar) : asset('default/profile.png'),
        ];
    }
}
