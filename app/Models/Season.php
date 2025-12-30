<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Season extends Model
{
    
    use SoftDeletes;

    protected $fillable = [
        'name',
        'year',
        'blanket_start_date',
        'blanket_end_date',
        'is_active',
    ];

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

}
