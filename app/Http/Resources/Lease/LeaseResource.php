<?php

namespace App\Http\Resources\Lease;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Property\PropertyResource;

class LeaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'rent_amount' => $this->rent_amount,
            'deposit_amount' => $this->deposit_amount,
            'property' => new PropertyResource($this->whenLoaded('property')),
        ];
    }
}
