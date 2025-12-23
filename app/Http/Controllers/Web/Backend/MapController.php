<?php

namespace App\Http\Controllers\Web\Backend;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\Work;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MapController extends Controller
{
    // init google map view with all teams and first team works
    public function globalMap()
    {
        $teams = Team::all();
        $firstTeam = $teams->first();

        $works = $firstTeam
            ? Work::where('team_id', $firstTeam->id)
            ->select(
                'id',
                'title',
                'description',
                'location',
                'latitude',
                'longitude',
                'start_datetime',
                'end_datetime',
                'is_all_day',
                'is_completed',
                'is_rescheduled'
            )
            ->get()
            : collect(); // empty if no team exist

        $works->transform(function ($work) {
            if ($work->is_all_day && $work->start_datetime) {
                $work->formatted_datetime = Carbon::parse($work->start_datetime)->format('M d, Y') . ' (All Day)';
            } elseif ($work->start_datetime) {
                $start = Carbon::parse($work->start_datetime)->format('M d, Y h:i A');
                $end = $work->end_datetime
                    ? Carbon::parse($work->end_datetime)->format('h:i A')
                    : '';
                $work->formatted_datetime = $start . ($end ? " - $end" : '');
            } else {
                $work->formatted_datetime = 'No date set';
            }

            return $work;
        });

        return view('backend.layouts.map.global_map', compact('teams', 'works', 'firstTeam'));
    }

    // filter works by team id and return as json
    public function filterWorksByTeam($teamId)
    {
        $works = Work::where('team_id', $teamId)
            ->select(
                'id',
                'title',
                'description',
                'location',
                'latitude',
                'longitude',
                'start_datetime',
                'end_datetime',
                'is_all_day',
                'is_completed',
                'is_rescheduled'
            )
            ->get();

        $works->transform(function ($work) {
            if ($work->is_all_day && $work->start_datetime) {
                $work->formatted_datetime = Carbon::parse($work->start_datetime)->format('M d, Y') . ' (All Day)';
            } elseif ($work->start_datetime) {
                $start = Carbon::parse($work->start_datetime)->format('M d, Y h:i A');
                $end = $work->end_datetime
                    ? Carbon::parse($work->end_datetime)->format('h:i A')
                    : '';
                $work->formatted_datetime = $start . ($end ? " - $end" : '');
            } else {
                $work->formatted_datetime = 'No date set';
            }

            return $work;
        });

        return response()->json($works);
    }

    // search teams by name and return as json
    public function searchTeams(Request $request)
    {
        $query = $request->query('query');
        $teams = Team::where('name', 'like', "%{$query}%")->get(['id', 'name']);
        return response()->json($teams);
    }
}
