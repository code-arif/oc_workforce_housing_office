<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'unit_id',
        'room_number',
        'name',
        'gender_designation',
        'is_active',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
