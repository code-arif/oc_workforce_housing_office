<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        'tenant_id',
        'bed_id',
        'lease_id',
        'invoice_id',
        'payment_id',
        'transaction_number',
        'type',
        'entry_type',
        'amount',
        'transaction_date',
        'description',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'transaction_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
