<?php

namespace App\Models\Lease;

use App\Models\Lease;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'lease_template_id',
        'tenant_id',
        'rendered_content',
        'admin_signature',
        'tenant_signature',
        'admin_signed_at',
        'tenant_signed_at',
        'status'
    ];

    protected $casts = [
        'admin_signed_at' => 'datetime',
        'tenant_signed_at' => 'datetime'
    ];

    /**
     * Get the lease for this document
     */
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get the template used for this document
     */
    public function leaseTemplate()
    {
        return $this->belongsTo(LeaseTemplate::class);
    }

    /**
     * Alias for leaseTemplate for compatibility
     */
    public function template()
    {
        return $this->belongsTo(LeaseTemplate::class, 'lease_template_id');
    }

    /**
     * Get the tenant for this document
     */
    // public function tenant()
    // {
    //     return $this->belongsTo(Tenant::class);
    // }

    /**
     * Check if document is fully signed
     */
    public function isFullySigned()
    {
        return !empty($this->admin_signature) && !empty($this->tenant_signature);
    }

    /**
     * Check if admin has signed
     */
    public function isAdminSigned()
    {
        return !empty($this->admin_signature);
    }

    /**
     * Check if tenant has signed
     */
    public function isTenantSigned()
    {
        return !empty($this->tenant_signature);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'signed' => 'success',
            'pending_signatures' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scope to get only signed documents
     */
    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    /**
     * Scope to get pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending_signatures');
    }

    /**
     * Update status based on signatures
     */
    public function updateStatus()
    {
        if ($this->isFullySigned()) {
            $this->status = 'signed';
        } elseif ($this->isAdminSigned() || $this->isTenantSigned()) {
            $this->status = 'pending_signatures';
        } else {
            $this->status = 'draft';
        }

        $this->save();
    }
}