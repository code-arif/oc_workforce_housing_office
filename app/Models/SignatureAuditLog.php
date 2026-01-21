<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureAuditLog extends Model
{
    protected $fillable = [
        'lease_document_id',
        'action',
        'user_id',
        'user_type',
        'user_ip_address',
        'user_agent',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Get the lease document this log belongs to
     */
    public function leaseDocument(): BelongsTo
    {
        return $this->belongsTo(LeaseDocument::class);
    }

    /**
     * Log an action for signature audit trail
     */
    public static function logAction(
        int $leaseDocumentId,
        string $action,
        ?int $userId = null,
        string $userType = 'admin',
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $metadata = null,
        ?string $notes = null
    ): self {
        return self::create([
            'lease_document_id' => $leaseDocumentId,
            'action' => $action,
            'user_id' => $userId,
            'user_type' => $userType,
            'user_ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
            'notes' => $notes,
        ]);
    }     

    /**
     * Scope to get logs for a specific action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs created within a date range
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
