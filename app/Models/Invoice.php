<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id', 'tenant_id', 'invoice_number', 'amount', 
        'total_amount', 'paid_amount', 'balance_due', 'issue_date',
        'due_date', 'type', 'status', 'is_first_invoice', 
        'includes_deposit', 'paid_at', 'notes', 'metadata'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'metadata' => 'array',
        'is_first_invoice' => 'boolean',
        'includes_deposit' => 'boolean',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isOverdue()
    {
        return $this->status !== 'PAID' && 
               $this->status !== 'CANCELLED' && 
               $this->due_date < now();
    }

    public function isPaid()
    {
        return $this->status === 'PAID';
    }

    public function isCancelled()
    {
        return $this->status === 'CANCELLED';
    }

    public function isPartial()
    {
        return $this->status === 'PARTIAL';
    }

    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->balance_due = $this->total_amount - $totalPaid;

        if ($totalPaid >= $this->total_amount) {
            $this->status = 'PAID';
            $this->paid_at = now();
        } elseif ($totalPaid > 0) {
            $this->status = 'PARTIAL';
        } else {
            $this->status = $this->isOverdue() ? 'OVERDUE' : 'UNPAID';
        }

        $this->save();
    }
}
