<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'image',
        'status',
        'order',
        'title',
        'location',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer'
    ];
}
