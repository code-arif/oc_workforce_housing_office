<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'description',
        'is_visible',
        'is_default',
        'google_calendar_id',
        'last_synced_at',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_default' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the calendar
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all works for this calendar
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /**
     * Scope for visible calendars only
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for user's calendars
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get calendar with event count
     */
    public function getEventCountAttribute()
    {
        return $this->works()->count();
    }
}
