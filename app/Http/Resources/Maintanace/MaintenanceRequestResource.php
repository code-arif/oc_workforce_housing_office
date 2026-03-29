<?php

namespace App\Http\Resources\Maintanace;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        // Direct FK relationships
        $unit = $this->unitModel;
        $room = $this->room;
        $bed  = $this->bed;

        // Fallback: lease assignment
        $activeLease = $this->tenant?->activeLease;
        $property    = $this->property ?? $activeLease?->property;

        if (!$unit || !$room || !$bed) {
            $assignment = $activeLease?->currentAssignment;
            $leaseBed   = $assignment?->bed;
            $leaseRoom  = $leaseBed?->room;
            $leaseUnit  = $leaseRoom?->unit;
            $bed        = $bed  ?? $leaseBed;
            $room       = $room ?? $leaseRoom;
            $unit       = $unit ?? $leaseUnit;
        }

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'category'         => $this->category,
            'description'      => $this->description,
            'status'           => $this->status,
            'is_urgent'        => (bool) $this->is_urgent,
            'grant_permission' => (bool) $this->grant_permission,
            'created_at'       => $this->created_at?->toDateTimeString(),
            'updated_at'       => $this->updated_at?->toDateTimeString(),

            // Location hierarchy
            'location' => [
                'property' => $property ? [
                    'id'      => $property->id,
                    'name'    => $property->name,
                    'address' => $property->address,
                ] : null,
                'unit' => $unit ? [
                    'id'   => $unit->id,
                    'name' => $unit->name,
                ] : null,
                'room' => $room ? [
                    'id'          => $room->id,
                    'name'        => $room->name ?? 'Room ' . $room->room_number,
                    'room_number' => $room->room_number,
                ] : null,
                'bed' => $bed ? [
                    'id'         => $bed->id,
                    'bed_number' => $bed->bed_number,
                    'bed_label'  => $bed->bed_label ?? 'Bed ' . $bed->bed_number,
                    'base_rent'  => $bed->base_rent,
                ] : null,
            ],

            // Lease info
            'lease' => $activeLease ? [
                'id'                => $activeLease->id,
                'status'            => $activeLease->status,
                'start_date'        => $activeLease->start_date,
                'end_date'          => $activeLease->end_date,
                'rent_amount'       => $activeLease->rent_amount,
                'payment_frequency' => $activeLease->payment_frequency,
            ] : null,

            // Attachments
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(fn($a) => [
                    'id'   => $a->id,
                    'path' => asset('storage/' . $a->attachment_path),
                    'type' => pathinfo($a->attachment_path, PATHINFO_EXTENSION),
                ]);
            }),
        ];
    }
}
