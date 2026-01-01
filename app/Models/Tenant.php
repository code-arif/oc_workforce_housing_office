<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Tenant extends Authenticatable implements JWTSubject
{

    protected $fillable = [
        'user_id',
        'application_source',
        'status',
        'move_in_date',
        'arrival_date',
        'date_of_birth',
        'gender'
    ];

    // relation with tenant profile table
    public function profile()
    {
        return $this->hasOne(TenantProfile::class);
    }

    // relation with tenant address table
    public function address()
    {
        return $this->hasOne(TenantAddress::class);
    }

    // relation with employment history table
    public function employments()
    {
        return $this->hasMany(TenantEmploymentHistory::class);
    }

    // relation with emergency contact table
    public function emergencyContacts()
    {
        return $this->hasMany(TenantEmergencyContact::class);
    }

    // relation with tenant document table
    public function documents()
    {
        return $this->hasMany(TenantDocument::class);
    }

    // relation with user table
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
