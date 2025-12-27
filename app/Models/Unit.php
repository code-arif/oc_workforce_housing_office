<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_unit');
    }
}
