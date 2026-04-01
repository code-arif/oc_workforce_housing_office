<?php

namespace App\Models;

use App\Models\Lease\LeaseDocument;
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
        'bed_assignment_pending',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'payment_frequency',
        'deposit_collected',
        'send_for_signature',
        'send_welcome_email',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deposit_collected' => 'boolean',
        'send_for_signature' => 'boolean',
        'send_welcome_email' => 'boolean',
        'bed_assignment_pending' => 'boolean',
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
        return $this->hasMany(LeaseAssignment::class, 'lease_id');
    }

    public function documents()
    {
        return $this->hasMany(LeaseDocument::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the payment schedules for the lease
     */
    public function paymentSchedules()
    {
        return $this->hasMany(LeasePaymentSchedule::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoices for the lease
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }


    /**
     * Get the user who created the lease
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if lease is active
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if lease is expired
     */
    public function isExpired()
    {
        return $this->end_date < now();
    }

    /**
     * Check if lease is expiring soon (within 90 days)
     */
    public function isExpiringSoon()
    {
        return $this->end_date->between(now(), now()->addDays(90));
    }

    /**
     * Get days remaining until lease ends
     */
    public function daysRemaining()
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Scope for active leases
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope for expiring leases
     */
    public function scopeExpiring($query, $days = 90)
    {
        return $query->where('status', 'ACTIVE')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Check if lease needs bed assignment
     */
    public function needsBedAssignment()
    {
        return $this->bed_assignment_pending ||
            !$this->assignments()->whereNotNull('bed_id')->where('is_current', true)->exists();
    }

    /**
     * Scope for leases pending bed assignment
     */
    public function scopePendingBedAssignment($query)
    {
        return $query->where('bed_assignment_pending', true);
    }

    /**
     * Get current bed assignment
     */
    // public function currentAssignment()
    // {
    //     return $this->assignments()->where('is_current', true)->first();
    // }

    public function currentAssignment()
    {
        return $this->hasOne(LeaseAssignment::class)
            ->where('is_current', true)
            ->with([
                'bed:id,room_id,bed_number,bed_label,base_rent',
                'bed.room:id,unit_id,room_number,name,gender_designation',
                'bed.room.unit:id,property_id,name,gender_designation',
            ]);
    }
}
