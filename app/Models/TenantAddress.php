<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAddress extends Model
{
    protected $fillable = [
        'tenant_id',
        'address_line1',
        'city',
        'state',
        'zip',
        'country'
    ];

    // reverse relation with tenant table
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
