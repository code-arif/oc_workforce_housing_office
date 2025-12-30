<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'tenant_id',
        'invoice_number',
        'amount',
        'due_date',
        'type',
        'status',
        'generated_at',
        'paid_at',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    // public function tenant()
    // {
    //     return $this->belongsTo(Tenant::class);
    // }

    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
