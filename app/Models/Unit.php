<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'name',
        'gender_designation',
        'is_active',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function totalRooms()
    {
        return $this->rooms()->count();
    }

    public function totalBeds()
    {
        return Bed::whereIn('room_id', $this->rooms()->pluck('id'))->count();
    }
}
