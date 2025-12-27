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
