<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\LoggableActivity;

class Payment extends Model
{
    use LoggableActivity;
    protected $fillable = [
        'invoice_id', 'tenant_id', 'lease_id', 'bed_id',
        'payment_number', 'amount', 'base_amount', 'processing_fee', 'total_charged', 
        'payment_date', 'deposit_date', 'payment_method',
        'reference_number', 'gateway_transaction_id', 'stripe_payment_intent_id', 
        'payment_type', 'paid_by', 'recorded_by', 'note', 'metadata',
        'review_status', 'reviewed_at', 'reviewed_by', 'review_note',
        'status', 'void_reason', 'voided_by', 'voided_at'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'deposit_date' => 'date',
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Scope for pending review payments
     */
    public function scopePendingReview($query)
    {
        return $query->where(function($q) {
            $q->where('review_status', 'pending')
              ->orWhereNull('review_status');
        });
    }

    /**
     * Scope for confirmed payments
     */
    public function scopeConfirmed($query)
    {
        return $query->where('review_status', 'confirmed');
    }

    /**
     * Check if payment is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->review_status === 'confirmed';
    }

    /**
     * Check if payment needs review
     */
    public function needsReview(): bool
    {
        return in_array($this->review_status, ['pending', null]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = 'PAY-' . str_pad(
                    self::max('id') + 1, 
                    6, 
                    '0', 
                    STR_PAD_LEFT
                );
            }

            if ($payment->payment_method === 'cash') {
                $payment->review_status = 'confirmed';
                $payment->reviewed_at = $payment->reviewed_at ?? now();
                $payment->reviewed_by = $payment->reviewed_by
                    ?? $payment->recorded_by
                    ?? Auth::id();
            }
        });

        static::created(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });
    }
}
