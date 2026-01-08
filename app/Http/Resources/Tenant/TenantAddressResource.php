<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TenantAddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
        ];
    }
}
