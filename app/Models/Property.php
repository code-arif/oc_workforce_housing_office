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

    public function totalRooms()
    {
        return Room::whereIn('unit_id', $this->units()->pluck('id'))->count();
    }

    public function totalBeds()
    {
        return Bed::whereIn('room_id',
            Room::whereIn('unit_id', $this->units()->pluck('id'))->pluck('id')
        )->count();
    }

    public function leases()
    {
        return $this->hasMany(Lease::class, 'property_id', 'id');
    }

}
