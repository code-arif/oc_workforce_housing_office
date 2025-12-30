<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'bed_id',
        'assigned_at',
        'actual_move_in',
        'actual_move_out',
        'is_current',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    
}
