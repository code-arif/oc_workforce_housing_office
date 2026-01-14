<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'tenant_id', 'lease_id', 'bed_id',
        'payment_number', 'amount', 'payment_date', 'payment_method',
        'reference_number', 'gateway_transaction_id', 'payment_type',
        'paid_by', 'recorded_by', 'note', 'metadata'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'metadata' => 'array',
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
        });

        static::created(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });
    }
}
