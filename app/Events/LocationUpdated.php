<?php

namespace App\Events;

use App\Models\TeamLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;
    public $teamId;

    public function __construct(TeamLocation $location)
    {
        $this->location = $location->load(['user:id,name,avatar', 'team:id,name']);
        $this->teamId = $location->team_id;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('location-tracking');
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->location->id,
            'team_id' => $this->location->team_id,
            'team_name' => $this->location->team->name,
            'user_id' => $this->location->user_id,
            'user_name' => $this->location->user->name,
            'user_avatar' => $this->location->user->avatar,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'accuracy' => $this->location->accuracy ? (float) $this->location->accuracy : null,
            'speed' => $this->location->speed ? (float) $this->location->speed : null,
            'bearing' => $this->location->bearing ? (float) $this->location->bearing : null,
            'altitude' => $this->location->altitude ? (float) $this->location->altitude : null,
            'battery_level' => $this->location->battery_level,
            'is_mock_location' => (bool) $this->location->is_mock_location,
            'activity_type' => $this->location->activity_type,
            'status' => $this->location->status,
            'tracked_at' => $this->location->tracked_at->toIso8601String(),
            'timestamp' => now()->toIso8601String()
        ];
    }
}
