<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'room_number',
        'description',
        'gender_designation',
        'is_active',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
