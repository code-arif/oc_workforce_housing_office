<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamLocation;
use App\Models\Work;
use App\Models\WorkTracking;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    /**
     * Show tracking dashboard
     */
    public function index()
    {
        $teams = Team::withCount('users')->orderBy('name')->get();

        return view('backend.layouts.map.tracking', compact('teams'));
    }

    /**
     * Get all locations for map (AJAX)
     */
    public function getLocations(Request $request)
    {
        try {
            $teamId = $request->get('team_id');

            $query = TeamLocation::select(
                'team_locations.id',
                'team_locations.team_id',
                'team_locations.user_id',
                'team_locations.latitude',
                'team_locations.longitude',
                'team_locations.accuracy',
                'team_locations.speed',
                'team_locations.bearing',
                'team_locations.altitude',
                'team_locations.battery_level',
                'team_locations.status',
                'team_locations.tracked_at',
                'teams.name as team_name',
                'users.name as user_name',
                'users.avatar as user_avatar',
                'team_users.is_leader'
            )
                ->join('teams', 'teams.id', '=', 'team_locations.team_id')
                ->join('users', 'users.id', '=', 'team_locations.user_id')
                ->join('team_users', function ($join) {
                    $join->on('team_users.team_id', '=', 'team_locations.team_id')
                        ->on('team_users.user_id', '=', 'team_locations.user_id')
                        ->where('team_users.is_leader', true);
                })
                ->whereIn('team_locations.id', function ($subQuery) {
                    $subQuery->select(DB::raw('MAX(id)'))
                        ->from('team_locations')
                        ->where('tracked_at', '>=', now()->subMinutes(10))
                        ->where('status', 'active')
                        ->groupBy('team_id', 'user_id');
                });

            // Filter by specific team if requested
            if ($teamId) {
                $query->where('team_locations.team_id', $teamId);
            }

            $locations = $query->orderBy('team_locations.tracked_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $locations,
                'count' => $locations->count(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch locations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    /**
     * Get team location route/path with work polylines
     */
    public function getTeamRoute(Request $request, $teamId)
    {
        try {
            $hours = $request->get('hours', 24);

            // Verify team exists
            $team = Team::findOrFail($teamId);

            // Get location path
            $locations = TeamLocation::select(
                'id',
                'user_id',
                'latitude',
                'longitude',
                'speed',
                'bearing',
                'tracked_at'
            )
                ->where('team_id', $teamId)
                ->where('tracked_at', '>=', now()->subHours($hours))
                ->where('status', 'active')
                ->orderBy('tracked_at', 'asc')
                ->get();

            // Get assigned works with status
            $works = Work::select(
                'works.id',
                'works.title',
                'works.description',
                'works.location',
                'works.latitude',
                'works.longitude',
                'works.geofence_radius',
                'works.start_datetime',
                'works.end_datetime',
                'works.is_completed',
                'works.is_rescheduled',
                'work_trackings.status as tracking_status',
                'work_trackings.started_at',
                'work_trackings.completed_at',
                'work_trackings.rescheduled_at',
                'work_trackings.total_distance',
                'work_trackings.total_duration'
            )
                ->leftJoin('work_trackings', function ($join) use ($teamId) {
                    $join->on('work_trackings.work_id', '=', 'works.id')
                        ->where('work_trackings.team_id', '=', $teamId);
                })
                ->where('works.team_id', $teamId)
                ->whereNotNull('works.latitude')
                ->whereNotNull('works.longitude')
                ->orderBy('works.start_datetime', 'desc')
                ->get();

            // Calculate route statistics
            $stats = [
                'total_distance' => $this->calculateTotalDistance($locations),
                'average_speed' => $locations->avg('speed'),
                'max_speed' => $locations->max('speed'),
                'duration_minutes' => $this->calculateDuration($locations),
                'total_points' => $locations->count(),
                'total_works' => $works->count(),
                'completed_works' => $works->where('tracking_status', 'completed')->count(),
                'in_progress_works' => $works->where('tracking_status', 'in_progress')->count(),
                'pending_works' => $works->where('tracking_status', 'pending')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'team' => [
                        'id' => $team->id,
                        'name' => $team->name
                    ],
                    'route' => $locations,
                    'works' => $works,
                    'stats' => $stats
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch team route', [
                'team_id' => $teamId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch team route',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    /**
     * Get all active teams with stats
     */
    public function getActiveTeams()
    {
        try {
            $teams = Team::select(
                'teams.id',
                'teams.name',
                DB::raw('COUNT(DISTINCT tl.user_id) as active_leaders'),
                DB::raw('MAX(tl.tracked_at) as last_update'),
                DB::raw('COUNT(DISTINCT w.id) as total_works'),
                DB::raw('SUM(CASE WHEN wt.status = "completed" THEN 1 ELSE 0 END) as completed_works'),
                DB::raw('SUM(CASE WHEN wt.status = "in_progress" THEN 1 ELSE 0 END) as in_progress_works'),
                DB::raw('SUM(CASE WHEN wt.status = "pending" THEN 1 ELSE 0 END) as pending_works')
            )
                ->leftJoin('team_locations as tl', function ($join) {
                    $join->on('tl.team_id', '=', 'teams.id')
                        ->where('tl.tracked_at', '>=', now()->subMinutes(10))
                        ->where('tl.status', 'active');
                })
                ->leftJoin('team_users as tu', function ($join) {
                    $join->on('tu.team_id', '=', 'teams.id')
                        ->where('tu.is_leader', true);
                })
                ->leftJoin('works as w', 'w.team_id', '=', 'teams.id')
                ->leftJoin('work_trackings as wt', 'wt.work_id', '=', 'w.id')
                ->groupBy('teams.id', 'teams.name')
                ->having('active_leaders', '>', 0)
                ->orderBy('last_update', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $teams,
                'count' => $teams->count(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch active teams', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teams',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    /**
     * Get team history with detailed analytics
     */
    public function getTeamHistory($teamId)
    {
        try {
            $hours = request()->get('hours', 24);

            // Verify team exists
            $team = Team::findOrFail($teamId);

            $history = TeamLocation::select(
                'team_locations.*',
                'users.name as user_name',
                'users.avatar as user_avatar'
            )
                ->join('users', 'users.id', '=', 'team_locations.user_id')
                ->where('team_locations.team_id', $teamId)
                ->where('team_locations.tracked_at', '>=', now()->subHours($hours))
                ->orderBy('team_locations.tracked_at', 'asc')
                ->get();

            // Calculate analytics
            $analytics = [
                'total_distance' => $this->calculateTotalDistance($history),
                'average_speed' => round($history->avg('speed'), 2),
                'max_speed' => $history->max('speed'),
                'min_speed' => $history->min('speed'),
                'duration_minutes' => $this->calculateDuration($history),
                'data_points' => $history->count(),
                'unique_users' => $history->pluck('user_id')->unique()->count(),
                'time_range' => [
                    'start' => $history->first()?->tracked_at,
                    'end' => $history->last()?->tracked_at
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'team' => [
                        'id' => $team->id,
                        'name' => $team->name
                    ],
                    'history' => $history,
                    'analytics' => $analytics
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch team history', [
                'team_id' => $teamId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch team history',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    /**
     * Calculate total distance using Haversine formula
     */
    private function calculateTotalDistance($locations)
    {
        if ($locations->count() < 2) {
            return 0;
        }

        $totalDistance = 0;
        $previousLocation = $locations->first();

        foreach ($locations->skip(1) as $location) {
            $distance = $this->haversineDistance(
                $previousLocation->latitude,
                $previousLocation->longitude,
                $location->latitude,
                $location->longitude
            );

            $totalDistance += $distance;
            $previousLocation = $location;
        }

        return round($totalDistance, 2); // km
    }

    /**
     * Haversine formula for distance calculation
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDiff = $lat2 - $lat1;
        $lonDiff = $lon2 - $lon1;

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos($lat1) * cos($lat2) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate tracking duration
     */
    private function calculateDuration($locations)
    {
        if ($locations->count() < 2) {
            return 0;
        }

        $first = $locations->first()->tracked_at;
        $last = $locations->last()->tracked_at;

        return round($first->diffInMinutes($last), 2);
    }
}
