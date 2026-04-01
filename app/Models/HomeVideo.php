<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeVideo extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'video_url',
        'key_points',
        'status',
        'order'
    ];

    protected $casts = [
        'key_points' => 'array',
        'status' => 'boolean',
        'order' => 'integer'
    ];

    public function getKeyPointsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function getVideoUrlAttribute($value)
    {
        return asset($value);
    }
}
