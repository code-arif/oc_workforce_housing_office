<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table = 'beds';

    protected $fillable = [
        'room_id',
        'bed_label',
        'bed_number',
        'description',
        'is_active',
    ];  

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
