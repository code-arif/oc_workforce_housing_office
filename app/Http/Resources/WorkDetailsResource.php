<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'location'      => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_completed'  => $this->is_completed,
            'description'   => $this->description,
            'is_rescheduled' => $this->is_rescheduled,
            'time'          => $this->is_all_day
                ? 'All Day'
                : ($this->start_datetime ? Carbon::parse($this->start_datetime)->format('g:i A') : null),
            'date'          => $this->start_datetime ? Carbon::parse($this->start_datetime)->format('d/m/Y') : null,
            'short_note'    => $this->note ?? 'N/A',
            'images'        => $this->images->map(function ($image) {
                return [
                    'url' => url($image->image_path)
                ];
            })->toArray(),
            'team'          => [
                'id' => $this->team?->id,
                'name' => $this->team?->name,
            ],
            'category'      => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
        ];
    }
}
