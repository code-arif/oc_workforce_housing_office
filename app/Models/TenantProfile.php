<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantProfile extends Model
{
    protected $fillable = [
        'tenant_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'passport_number',
        'national_id',
        'avatar'
    ];

    // reverse relation with tenant table
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If you store in "public" disk
        return asset($value);
        // OR if using Storage disk 'public'
        // return Storage::url($value);
    }
}
