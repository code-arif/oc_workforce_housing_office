<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenities extends Model
{
    protected $table = 'amenities';

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function beds()
    {
        return $this->belongsToMany(Bed::class, 'bed_amenities', 'amenity_id', 'bed_id');
    }
}
