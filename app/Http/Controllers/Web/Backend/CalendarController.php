<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\Work;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CalendarController extends Controller
{
    //make calendar
    public function calendar(Request $request)
    {
        $teamId = $request->get('team_id');
        $teams = Team::all();

        $query = Work::with('team')->whereNotNull('work_date');

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $works = $query->get();

        $events = $works->map(function ($work) {
            if (!$work->work_date) {
                return null;
            }

            $cleanStartTime = null;
            $cleanEndTime = null;

            if ($work->start_time && preg_match('/^\d{2}:\d{2}:\d{2}$/', $work->start_time)) {
                $cleanStartTime = $work->start_time;
            }

            if ($work->end_time && preg_match('/^\d{2}:\d{2}:\d{2}$/', $work->end_time)) {
                $cleanEndTime = $work->end_time;
            }

            $teamName = $work->team ? $work->team->name : 'No Team';
            $eventTitle = $work->title . ' (' . $teamName . ')'; // Concatenate title with team name in parentheses

            if (!$cleanStartTime || !$cleanEndTime) {
                return [
                    'id' => $work->id,
                    'title' => $eventTitle,
                    'start' => $work->work_date,
                    'description' => $work->description ?? 'No description',
                    'allDay' => true,
                    'backgroundColor' => $work->is_completed ? '#34c38f' : '#60a5fa',
                    'borderColor' => $work->is_completed ? '#2a926f' : '#1e88e5',
                    'extendedProps' => [
                        'location' => $work->location ?? 'Not specified',
                        'status' => $work->status,
                        'is_rescheduled' => $work->is_rescheduled,
                        'note' => $work->note ?? 'No notes',
                        'team' => $teamName,
                    ],
                ];
            }

            $startStr = $work->work_date . ' ' . $cleanStartTime;
            $endStr = $work->work_date . ' ' . $cleanEndTime;

            try {
                return [
                    'id' => $work->id,
                    'title' => $eventTitle,
                    'start' => Carbon::parse($startStr)->toISOString(),
                    'end' => Carbon::parse($endStr)->toISOString(),
                    'description' => $work->description ?? 'No description',
                    'allDay' => false,
                    'backgroundColor' => $work->is_completed ? '#34c38f' : '#60a5fa',
                    'borderColor' => $work->is_completed ? '#2a926f' : '#1e88e5',
                    'extendedProps' => [
                        'location' => $work->location ?? 'Not specified',
                        'status' => $work->status,
                        'is_rescheduled' => $work->is_rescheduled,
                        'note' => $work->note ?? 'No notes',
                        'team' => $teamName,
                    ],
                ];
            } catch (\Exception $e) {
                return [
                    'id' => $work->id,
                    'title' => $eventTitle,
                    'start' => $work->work_date,
                    'description' => $work->description ?? 'No description',
                    'allDay' => true,
                    'backgroundColor' => $work->is_completed ? '#34c38f' : '#60a5fa',
                    'borderColor' => $work->is_completed ? '#2a926f' : '#1e88e5',
                    'extendedProps' => [
                        'location' => $work->location ?? 'Not specified',
                        'status' => $work->status,
                        'is_rescheduled' => $work->is_rescheduled,
                        'note' => $work->note ?? 'No notes',
                        'team' => $teamName,
                    ],
                ];
            }
        })->filter()->values();

        return view('backend.layouts.calendar.index', compact('events', 'teams', 'teamId'));
    }
}
