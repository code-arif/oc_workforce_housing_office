<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeasePaymentSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'due_date',
        'amount',
        'period_start',
        'period_end',
        'description',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    // public function payments()
    // {
    //     return $this->hasMany(LeasePayment::class);
    // }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
