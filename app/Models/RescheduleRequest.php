<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RescheduleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'team_id',
        'start_datetime',
        'end_datetime',
        'is_all_day',
        'status',
        'note'
    ];

    protected $casts = [
        'work_date' => 'date'
    ];

    // relation with work table
    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    // relation with team table
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
