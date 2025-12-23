<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTracking extends Model
{
    protected $table = 'work_trackings';

    protected $fillable = [
        'work_id',
        'team_id',
        'started_at',
        'completed_at',
        'rescheduled_at',
        'completion_note',
        'total_distance',
        'total_duration',
        'status'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'total_distance' => 'decimal:2',
        'total_duration' => 'integer'
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
