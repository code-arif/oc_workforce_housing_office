<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Lease\LeaseDocument;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\LoggableActivity;

class Tenant extends Authenticatable implements JWTSubject
{
    use HasFactory, LoggableActivity;

    protected $fillable = [
        'application_id',
        'application_source',
        'status',
        'move_in_date',
        'arrival_date',
        'date_of_birth',
        'gender',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'approval_token',
        'approval_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'otp',
        'reset_password_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'move_in_date' => 'date',
        'arrival_date' => 'date',
        'otp_expires_at' => 'datetime',
        'reset_password_token_expire_at' => 'datetime',
        'approval_token_expires_at' => 'datetime',
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

    public function getRoleNames()
    {
        return collect(['Tenant']);
    }

    /**
     * ----------------------------
     * Helper funciton for tenant
     * ----------------------------
     */

    /**
     * Generate approval token for admin actions
     */
    public function generateApprovalToken()
    {
        $this->approval_token = Str::random(64);
        $this->approval_token_expires_at = now()->addMonth(); // Token valid for 1 month
        $this->save();

        return $this;
    }

    public function clearApprovalToken()
    {
        $this->approval_token = null;
        $this->approval_token_expires_at = null;
        $this->save();
    }


    /**
     * Check if approval token is valid
     */
    public function isApprovalTokenValid()
    {
        if (!$this->approval_token) {
            return false;
        }

        if ($this->approval_token_expires_at && now()->isAfter($this->approval_token_expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Generate OTP for password reset
     */
    public function generateOTP()
    {
        $this->otp = rand(100000, 999999);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->save();

        return $this;
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($otp)
    {
        if ($this->otp !== $otp) {
            return false;
        }

        if (now()->isAfter($this->otp_expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->reset_password_token = Str::random(64);
        $this->reset_password_token_expire_at = now()->addMonth(); // Token valid for 1 month
        $this->save();

        return $this;
    }

    /**
     * Clear password reset data
     */
    public function clearPasswordResetData()
    {
        $this->otp = null;
        $this->otp_expires_at = null;
        $this->reset_password_token = null;
        $this->reset_password_token_expire_at = null;
        $this->save();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSelfApplied($query)
    {
        return $query->where('application_source', 'self');
    }

    public function scopeAdminCreated($query)
    {
        return $query->where('application_source', 'admin');
    }

    // relation with lease table
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function leaseDocuments()
    {
        return $this->hasManyThrough(LeaseDocument::class, Lease::class);
    }

    public function application()
    {
        return $this->hasOne(Application::class, 'id', 'application_id');
    }

    /**
     * Tenant's active lease
     */
    public function activeLease()
    {
        return $this->hasOne(Lease::class)
            ->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
            ->with([
                'property:id,name,address',
                'currentAssignment.bed.room.unit',
            ])
            ->latest();
    }
}
