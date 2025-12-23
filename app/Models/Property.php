<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'properties';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'property_type_id',
        'image_path',
        'latitude',
        'longitude',
    ];

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }
}
