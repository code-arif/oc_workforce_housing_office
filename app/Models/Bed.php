<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bed extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'beds';

    protected $fillable = [
        'room_id',
        'bed_number',
        'bed_label',
        'base_rent',
        'is_occupied',
    ];  

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenities::class, 'bed_amenities', 'bed_id', 'amenity_id');
    }
}
