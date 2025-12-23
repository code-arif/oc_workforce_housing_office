<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MapWorkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'location'      => $this->location,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'is_completed'  => $this->is_completed,
        ];
    }
}
