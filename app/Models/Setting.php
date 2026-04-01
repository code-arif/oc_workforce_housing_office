<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    public function getLogoAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    public function getFaviconAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}

