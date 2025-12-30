<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use SoftDeletes;
    protected $table = 'leases';

    protected $fillable = [
        'tenant_id',
        'property_id',
        'season_id',
        'status',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'payment_frequency',
        'notes',
        'created_by',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function assignments()
    {
        return $this->hasMany(LeaseAssignment::class);
    }

    public function documents()
    {
        return $this->hasMany(LeaseDocument::class);
    }

    // public function tenant()
    // {
    //     return $this->belongsTo(Tenant::class);
    // }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
