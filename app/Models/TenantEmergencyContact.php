<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantEmergencyContact extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'relationship'
    ];

    // reverse relation with tenant table
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
