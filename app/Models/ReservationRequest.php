<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRequest extends Model
{
    protected $table = 'reservation_requests';

    protected $fillable = [
        'company_name',
        'industry',
        'company_address',
        'employee_count',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'reservation_item',
        'notes',
    ];

    protected $casts = [
        'reservation_item' => 'array',
    ];
}
