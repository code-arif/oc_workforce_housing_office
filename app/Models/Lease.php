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
}
