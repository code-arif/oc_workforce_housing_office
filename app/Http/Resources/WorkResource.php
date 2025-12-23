<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use Carbon\Carbon;

class WorkResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'location'      => $this->location,
            'time'          => $this->is_all_day
                ? 'All Day'
                : ($this->start_datetime ? Carbon::parse($this->start_datetime)->format('g:i A') : null),
            'date'          => $this->start_datetime ? Carbon::parse($this->start_datetime)->format('d/m/Y') : null,
            'is_all_day'    => $this->is_all_day,
            'is_completed'  => $this->is_completed,
            'is_rescheduled' => $this->is_rescheduled,
            'category'      => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ],
        ];
    }
}
