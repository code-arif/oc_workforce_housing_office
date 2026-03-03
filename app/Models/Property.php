<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'properties';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'property_type_id',
        'image_path',
        'latitude',
        'longitude',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',

        // stripe payment related fields
        'stripe_account_id',
        'stripe_account_status',
        'stripe_onboarding_completed',
        'stripe_account_data',
        'stripe_connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stripe_onboarding_completed' => 'boolean',
        'stripe_account_data' => 'array',
        'stripe_connected_at' => 'datetime',
    ];

    public function getActiveAttribute($value)
    {
        return (bool) $value;
    }


    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function setCreatedByAttribute($value)
    {
        $this->attributes['created_by'] = $value ?? auth()->id();
    }

    public function setUpdatedByAttribute($value)
    {
        $this->attributes['updated_by'] = $value ?? auth()->id();
    }

    public function setDeletedByAttribute($value)
    {
        $this->attributes['deleted_by'] = $value ?? auth()->id();
    }

    public function totalUnits()
    {
        return $this->units()->count();
    }

    /**
     * OPTIMIZED: Get total rooms count using a join instead of nested whereIn
     * Better performance at 100K+ records
     */
    public function totalRooms()
    {
        return Room::join('units', 'rooms.unit_id', '=', 'units.id')
            ->where('units.property_id', $this->id)
            ->count();
    }

    /**
     * OPTIMIZED: Get total beds count using joins instead of nested whereIn
     * Better performance at 100K+ records
     */
    public function totalBeds()
    {
        return Bed::join('rooms', 'beds.room_id', '=', 'rooms.id')
            ->join('units', 'rooms.unit_id', '=', 'units.id')
            ->where('units.property_id', $this->id)
            ->count();
    }

    /**
     * Get available beds count
     */
    public function availableBeds()
    {
        return Bed::join('rooms', 'beds.room_id', '=', 'rooms.id')
            ->join('units', 'rooms.unit_id', '=', 'units.id')
            ->where('units.property_id', $this->id)
            ->where('beds.is_occupied', false)
            ->count();
    }

    public function leases()
    {
        return $this->hasMany(Lease::class, 'property_id', 'id');
    }


    // Helper methods
    public function hasStripeConnected(): bool
    {
        return $this->stripe_account_id
            && $this->stripe_onboarding_completed
            && $this->stripe_account_status === 'active';
    }

    public function getStripeStatusBadgeAttribute(): string
    {
        return match ($this->stripe_account_status) {
            'active' => '<span class="badge bg-success">Connected</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'restricted' => '<span class="badge bg-danger">Restricted</span>',
            'disabled' => '<span class="badge bg-secondary">Disabled</span>',
            default => '<span class="badge bg-light text-dark">Not Connected</span>',
        };
    }
}
