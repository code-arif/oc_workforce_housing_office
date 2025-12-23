<?php

namespace App\Http\Controllers\Api;

use App\Events\LocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\TeamLocation;
use App\Models\Work;
use App\Models\WorkTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Batch location update with real-time broadcasting
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id',
            'device_id' => 'nullable|string',
            'locations' => 'required|array|min:1|max:50',
            'locations.*.latitude' => 'required|numeric|between:-90,90',
            'locations.*.longitude' => 'required|numeric|between:-180,180',
            'locations.*.accuracy' => 'nullable|numeric|min:0|max:1000',
            'locations.*.speed' => 'nullable|numeric|min:0',
            'locations.*.bearing' => 'nullable|numeric|between:0,360',
            'locations.*.altitude' => 'nullable|numeric',
            'locations.*.battery_level' => 'nullable|string',
            'locations.*.is_mock_location' => 'nullable|boolean',
            'locations.*.activity_type' => 'nullable|string',
            'locations.*.network_type' => 'nullable|string',
            'locations.*.tracked_at' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify team membership and tracking status
            $teamUser = DB::table('team_users')
                ->where('team_id', $request->team_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$teamUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this team'
                ], 403);
            }

            if (!$teamUser->is_tracking_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location tracking is disabled for your account'
                ], 403);
            }

            $savedLocations = [];
            $broadcastCount = 0;
            $geofenceAlerts = [];

            DB::beginTransaction();

            foreach ($request->locations as $index => $locationData) {
                // Skip mock locations
                if (isset($locationData['is_mock_location']) && $locationData['is_mock_location']) {
                    Log::warning('Mock location detected', [
                        'user_id' => auth()->id(),
                        'team_id' => $request->team_id
                    ]);
                    continue;
                }

                // Skip low accuracy readings (> 100m)
                if (isset($locationData['accuracy']) && $locationData['accuracy'] > 100) {
                    continue;
                }

                // Create location record
                $location = TeamLocation::create([
                    'team_id' => $request->team_id,
                    'user_id' => auth()->id(),
                    'device_id' => $request->device_id,
                    'latitude' => $locationData['latitude'],
                    'longitude' => $locationData['longitude'],
                    'accuracy' => $locationData['accuracy'] ?? null,
                    'speed' => $locationData['speed'] ?? null,
                    'bearing' => $locationData['bearing'] ?? null,
                    'altitude' => $locationData['altitude'] ?? null,
                    'battery_level' => $locationData['battery_level'] ?? null,
                    'is_mock_location' => false,
                    'activity_type' => $locationData['activity_type'] ?? null,
                    'network_type' => $locationData['network_type'] ?? null,
                    'status' => 'active',
                    'tracked_at' => $locationData['tracked_at']
                ]);

                $savedLocations[] = $location;

                // Broadcast only the latest location (last one in batch)
                if ($index === count($request->locations) - 1 && $teamUser->is_leader) {
                    try {
                        broadcast(new LocationUpdated($location))->toOthers();
                        $broadcastCount++;
                    } catch (\Exception $e) {
                        Log::error('Broadcasting failed', [
                            'error' => $e->getMessage(),
                            'location_id' => $location->id
                        ]);
                    }
                }

                // Check geofence for work assignments
                $geofenceResult = $this->checkWorkGeofence(
                    $request->team_id,
                    $locationData['latitude'],
                    $locationData['longitude']
                );

                if ($geofenceResult) {
                    $geofenceAlerts[] = $geofenceResult;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Locations updated successfully',
                'data' => [
                    'saved_count' => count($savedLocations),
                    'broadcast_count' => $broadcastCount,
                    'tracking_status' => 'active',
                    'next_update_interval' => 10, // seconds
                    'geofence_alerts' => $geofenceAlerts
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Location update failed', [
                'user_id' => auth()->id(),
                'team_id' => $request->team_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    /**
     * Check work geofence with entry/exit detection
     */
    private function checkWorkGeofence($teamId, $latitude, $longitude)
    {
        try {
            $activeWorks = Work::where('team_id', $teamId)
                ->where('is_completed', false)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            foreach ($activeWorks as $work) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    $work->latitude,
                    $work->longitude
                );

                $geofenceRadius = $work->geofence_radius ?? 50; // default 50m
                $isInside = $distance <= $geofenceRadius;

                $tracking = WorkTracking::firstOrCreate(
                    [
                        'work_id' => $work->id,
                        'team_id' => $teamId
                    ],
                    [
                        'status' => 'pending'
                    ]
                );

                // Geofence entry detection
                if ($isInside && $tracking->status === 'pending') {
                    $tracking->update([
                        'started_at' => now(),
                        'status' => 'in_progress'
                    ]);

                    Log::info("Team entered work geofence", [
                        'team_id' => $teamId,
                        'work_id' => $work->id,
                        'distance' => round($distance, 2) . 'm'
                    ]);

                    return [
                        'type' => 'geofence_entry',
                        'work_id' => $work->id,
                        'work_title' => $work->title,
                        'distance' => round($distance, 2),
                        'status' => 'in_progress'
                    ];
                }

                // Geofence exit detection (optional)
                if (!$isInside && $tracking->status === 'in_progress') {
                    Log::info("Team exited work geofence", [
                        'team_id' => $teamId,
                        'work_id' => $work->id,
                        'distance' => round($distance, 2) . 'm'
                    ]);
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Geofence check failed', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Haversine distance calculation (returns meters)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
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
     * Get current locations
     */
    public function getCurrentLocations()
    {
        try {
            // Step 1: Get latest location ID for each team-user combination
            $latestIds = DB::table('team_locations')
                ->select(DB::raw('MAX(id) as id'))
                ->groupBy('team_id', 'user_id')
                ->pluck('id');

            Log::info('Latest location IDs: ' . $latestIds->count());

            // Step 2: Get full data for those IDs
            $locations = TeamLocation::whereIn('id', $latestIds)
                ->with(['team:id,name', 'user:id,name,avatar'])
                ->get();

            Log::info('Locations found: ' . $locations->count());

            // Step 3: Enrich with team_user data
            $enrichedData = $locations->map(function ($loc) {
                $teamUser = DB::table('team_users')
                    ->where('team_id', $loc->team_id)
                    ->where('user_id', $loc->user_id)
                    ->first(['is_leader', 'is_tracking_active']);

                return [
                    'id' => $loc->id,
                    'team_id' => $loc->team_id,
                    'team_name' => optional($loc->team)->name,
                    'user_id' => $loc->user_id,
                    'user_name' => optional($loc->user)->name,
                    'user_avatar' => optional($loc->user)->avatar,
                    'latitude' => (float) $loc->latitude,
                    'longitude' => (float) $loc->longitude,
                    'accuracy' => (float) $loc->accuracy,
                    'speed' => (float) $loc->speed,
                    'bearing' => (float) $loc->bearing,
                    'battery_level' => $loc->battery_level,
                    'status' => $loc->status,
                    'tracked_at' => $loc->tracked_at,
                    'is_leader' => $teamUser ? (bool) $teamUser->is_leader : false,
                    'is_tracking_active' => $teamUser ? (bool) $teamUser->is_tracking_active : true,
                ];
            })->sortByDesc('tracked_at')->values();

            return response()->json([
                'success' => true,
                'data' => $enrichedData,
                'count' => $enrichedData->count(),
                'timestamp' => now()->toIso8601String()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch locations', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    /**
     * Get team location history
     */
    public function getTeamHistory($teamId, Request $request)
    {
        $hours = $request->get('hours', 24);

        try {
            $locations = TeamLocation::with('user:id,name,avatar')
                ->where('team_id', $teamId)
                ->where('tracked_at', '>=', now()->subHours($hours))
                ->orderBy('tracked_at', 'asc')
                ->get();

            $works = Work::where('team_id', $teamId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['tracking' => function ($query) use ($teamId) {
                    $query->where('team_id', $teamId);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'locations' => $locations,
                    'works' => $works,
                    'total_distance' => $this->calculateTotalDistance($locations),
                    'duration_minutes' => $this->calculateDuration($locations)
                ]
            ], 200);
        } catch (\Exception $e) {
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
     * Total distance calculation
     */
    private function calculateTotalDistance($locations)
    {
        if ($locations->count() < 2) return 0;

        $totalDistance = 0;
        $previousLocation = $locations->first();

        foreach ($locations->skip(1) as $location) {
            $distance = $this->calculateDistance(
                $previousLocation->latitude,
                $previousLocation->longitude,
                $location->latitude,
                $location->longitude
            );
            $totalDistance += $distance;
            $previousLocation = $location;
        }

        return round($totalDistance / 1000, 2); // km
    }

    /**
     * Calculate duration
     */
    private function calculateDuration($locations)
    {
        if ($locations->count() < 2) return 0;

        $first = $locations->first()->tracked_at;
        $last = $locations->last()->tracked_at;

        return $first->diffInMinutes($last);
    }
}
