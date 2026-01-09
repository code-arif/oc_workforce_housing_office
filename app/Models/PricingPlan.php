<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pricing_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'price_per_bed',
        'tenants_per_room',
        'amenities',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amenities' => 'array', 
        'price_per_bed' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Optional: Helper method to format price nicely
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price_per_bed, 0) . ' / per bed';
    }

    /**
     * Optional: Helper to check if a specific amenity exists
     */
    public function hasAmenity($amenity)
    {
        return in_array($amenity, $this->amenities ?? []);
    }
}
