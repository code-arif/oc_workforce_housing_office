<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaseDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'template_id',
        'document_url',
        'generated_document_content',
        'field_values',
        'tenant_signature_token',
        'tenant_signed_at',
        'tenant_signature_image',
        'tenant_ip_address',
        'admin_signature_token',
        'admin_signed_at',
        'admin_signature_image',
        'signature_metadata',
    ];

    protected $casts = [
        'field_values' => 'json',
        'signature_metadata' => 'json',
        'tenant_signed_at' => 'datetime',
        'admin_signed_at' => 'datetime',
    ];

    /**
     * Get the lease associated with this document
     */
    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get the template used for this document
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LeaseTemplate::class);
    }

    /**
     * Get all signature audit logs for this document
     */
    public function signatureAuditLog(): HasMany
    {
        return $this->hasMany(SignatureAuditLog::class);
    }

    /**
     * Check if document is signed by tenant
     */
    public function isTenantSigned(): bool
    {
        return !is_null($this->tenant_signed_at);
    }

    /**
     * Check if document is signed by admin
     */
    public function isAdminSigned(): bool
    {
        return !is_null($this->admin_signed_at);
    }

    /**
     * Check if document is fully signed by both parties
     */
    public function isFullySigned(): bool
    {
        return $this->isTenantSigned() && $this->isAdminSigned();
    }

    /**
     * Get signature status
     */
    public function getSignatureStatus(): array
    {
        return [
            'tenant_signed' => $this->isTenantSigned(),
            'admin_signed' => $this->isAdminSigned(),
            'fully_signed' => $this->isFullySigned(),
            'tenant_signed_at' => $this->tenant_signed_at,
            'admin_signed_at' => $this->admin_signed_at,
        ];
    }
}
