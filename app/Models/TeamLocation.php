<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/TeamLocation.php
class TeamLocation extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'bearing',
        'altitude',
        'battery_level',
        'is_mock_location',
        'activity_type',
        'status',
        'tracked_at',
        'device_id',
        'network_type',
        'signal_strength'
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
        'is_mock_location' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for recent locations
    public function scopeRecent($query, $minutes = 10)
    {
        return $query->where('tracked_at', '>=', now()->subMinutes($minutes));
    }

    // Scope for active status
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
