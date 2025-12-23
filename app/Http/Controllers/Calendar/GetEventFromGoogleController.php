<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GetEventFromGoogleController extends Controller
{
    /**
     * Get events with calendar filtering
     */
    public function getEvents(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            $teamId = $request->get('team_id');
            $status = $request->get('status');
            $categoryId = $request->get('category_id');
            $calendarIds = $request->get('calendar_ids');

            // Parse calendar IDs properly
            if ($calendarIds && is_string($calendarIds)) {
                $calendarIds = array_filter(explode(',', $calendarIds));
            }

            $query = Work::with(['team', 'category', 'calendar'])
                ->where('user_id', Auth::id())
                ->whereNull('deleted_at'); // Exclude soft deleted

            // Date range filter
            if ($start && $end) {
                $query->whereBetween('start_datetime', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            }

            // Calendar filter - FIXED
            if ($calendarIds && is_array($calendarIds) && count($calendarIds) > 0) {
                $query->whereIn('calendar_id', $calendarIds);
            } else {
                // Show only visible calendars by default
                $query->whereHas('calendar', function ($q) {
                    $q->where('user_id', Auth::id())
                        ->where('is_visible', true);
                });
            }

            // Team filter
            if ($teamId) {
                $query->where('team_id', $teamId);
            }

            // Status filter
            if ($status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($status === 'pending') {
                $query->where('is_completed', false);
            } elseif ($status === 'rescheduled') {
                $query->where('is_rescheduled', true);
            }

            // Category filter
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $works = $query->get();

            $events = $works->map(function ($work) {
                try {
                    $baseEvent = [
                        'id' => $work->id,
                        'title' => $work->title,
                        'description' => $work->description,
                        'location' => $work->location,
                        'backgroundColor' => $work->calendar ? $work->calendar->color : $this->getEventColor($work),
                        'borderColor' => $work->calendar ? $work->calendar->color : $this->getEventBorderColor($work),
                        'extendedProps' => [
                            'team' => $work->team ? $work->team->name : null,
                            'category' => $work->category ? $work->category->name : null,
                            'calendar_name' => $work->calendar ? $work->calendar->name : 'Default',
                            'calendar_id' => $work->calendar_id,
                            'completed' => $work->is_completed,
                            'rescheduled' => $work->is_rescheduled,
                            'latitude' => $work->latitude,
                            'longitude' => $work->longitude,
                            'note' => $work->note,
                            'google_event_id' => $work->google_event_id,
                            'is_all_day' => $work->is_all_day,
                        ],
                    ];

                    if ($work->is_all_day) {
                        $baseEvent['start'] = Carbon::parse($work->start_datetime)->toDateString();
                        $baseEvent['end'] = Carbon::parse($work->end_datetime)->addDay()->toDateString(); // FIXED: Add 1 day for FullCalendar
                        $baseEvent['allDay'] = true;
                    } else {
                        $baseEvent['start'] = Carbon::parse($work->start_datetime)->toIso8601String();
                        $baseEvent['end'] = Carbon::parse($work->end_datetime)->toIso8601String();
                    }

                    return $baseEvent;
                } catch (Exception $e) {
                    Log::error('Error processing work for calendar', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter()->values();

            return response()->json($events);
        } catch (Exception $e) {
            Log::error('getEvents error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load events',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getEventColor($work)
    {
        if ($work->is_completed) return '#10b981';
        if ($work->is_rescheduled) return '#f59e0b';
        return '#3b82f6';
    }

    private function getEventBorderColor($work)
    {
        if ($work->is_completed) return '#059669';
        if ($work->is_rescheduled) return '#d97706';
        return '#2563eb';
    }
}
