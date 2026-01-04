<?php

namespace App\Models;

use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Tenant extends Authenticatable implements JWTSubject
{

    protected $fillable = [
        'application_source',
        'status',
        'move_in_date',
        'arrival_date',
        'date_of_birth',
        'gender',
        'password',
        'email',
        'otp',
        'otp_expires_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'approval_token',
        'approval_token_expires_at'
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

    /**
     * ----------------------------
     * Helper funciton for tenant
     * ----------------------------
     */
    public function generateApprovalToken()
    {
        $this->approval_token = Str::random(60);
        $this->approval_token_expires_at = now()->addHours(72);
        $this->save();
    }

    public function clearApprovalToken()
    {
        $this->approval_token = null;
        $this->approval_token_expires_at = null;
        $this->save();
    }

    public function isApprovalTokenValid($token)
    {
        return $this->approval_token === $token &&
            $this->approval_token_expires_at &&
            $this->approval_token_expires_at->isFuture();
    }
}
