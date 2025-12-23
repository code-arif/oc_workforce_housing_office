<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $table = 'property_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    //create slug before saving
    // public function setSlugAttribute($value)
    // {
    //     $this->attributes['slug'] = str_slug($value);
    // }

    // public function properties()
    // {
    //     return $this->hasMany(Property::class);
    // }
}
