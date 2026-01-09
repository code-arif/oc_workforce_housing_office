<?php

namespace App\Models\Lease;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaseTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_filename',
        'file_type',
        'content',
        'placeholders',
        'signatures',
        'is_active'
    ];

    protected $casts = [
        'placeholders' => 'array',
        'signatures' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get all lease documents using this template
     */
    public function leaseDocuments()
    {
        return $this->hasMany(LeaseDocument::class);
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get placeholder count
     */
    public function getPlaceholderCountAttribute()
    {
        return is_array($this->placeholders) ? count($this->placeholders) : 0;
    }

    /**
     * Get signature count
     */
    public function getSignatureCountAttribute()
    {
        return is_array($this->signatures) ? count($this->signatures) : 0;
    }

    /**
     * Check if template has admin signature
     */
    public function hasAdminSignature()
    {
        if (!is_array($this->signatures)) {
            return false;
        }

        foreach ($this->signatures as $signature) {
            if (isset($signature['type']) && $signature['type'] === 'admin') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if template has tenant signature
     */
    public function hasTenantSignature()
    {
        if (!is_array($this->signatures)) {
            return false;
        }

        foreach ($this->signatures as $signature) {
            if (isset($signature['type']) && $signature['type'] === 'tenant') {
                return true;
            }
        }

        return false;
    }
}