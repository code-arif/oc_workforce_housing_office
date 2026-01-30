<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'industry',
        'company_address',
        'employee_count',
        'reservation_item',
        'notes',
    ];

    protected $casts = [
        'reservation_item' => 'array',
        'employee_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get full name of the applicant
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);

        return implode(' ', $parts);
    }

    /**
     * Check if application is individual type
     */
    public function isIndividual()
    {
        return $this->type === 'individual';
    }

    /**
     * Check if application is corporate type
     */
    public function isCorporate()
    {
        return $this->type === 'corporate';
    }

    /**
     * Check if application is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if application is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if application is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getReservationItemAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value ?? [];
    }
}
