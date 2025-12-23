<?php

namespace App\Models;

use App\Models\Calendar;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'calendar_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'start_datetime',
        'end_datetime',
        'is_all_day',
        'is_completed',
        'is_rescheduled',
        'note',
        'team_id',
        'category_id',
        'google_event_id',
        'google_synced_at',
    ];


    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_all_day' => 'boolean',
        'is_completed' => 'boolean',
        'is_rescheduled' => 'boolean',
        'google_synced_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected $dates = ['deleted_at'];


    // relation with team table
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // relation with category table
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // relation with work_images table
    public function images()
    {
        return $this->hasMany(WorkImage::class, 'work_id');
    }

    // relation with reschedule_requests table
    public function rescheduleRequests()
    {
        return $this->hasMany(RescheduleRequest::class, 'work_id');
    }

    // relation with reschedule_requests table
    public function request()
    {
        return $this->hasOne(RescheduleRequest::class, 'work_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeRescheduled($query)
    {
        return $query->where('is_rescheduled', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('work_date', Carbon::today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('work_date', '>=', Carbon::today())
            ->orderBy('work_date')
            ->orderBy('time');
    }

    public function scopeByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    // for work tracking in google map
    public function tracking()
    {
        return $this->hasOne(WorkTracking::class);
    }

    // Check if work has location
    public function hasLocation()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // calendar relation
    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Scope for specific calendar
     */
    public function scopeForCalendar($query, $calendarId)
    {
        return $query->where('calendar_id', $calendarId);
    }

    /**
     * Scope for visible calendars only
     */
    public function scopeVisibleCalendars($query, $userId)
    {
        return $query->whereHas('calendar', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_visible', true);
        });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeTrashed($query)
    {
        return $query->onlyTrashed();
    }
}
