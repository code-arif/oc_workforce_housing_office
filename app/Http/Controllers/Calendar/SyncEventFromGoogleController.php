<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;

class SyncEventFromGoogleController extends Controller
{
    protected $googleCalendar;

    // serivce injection
    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    // sync 3 month ago and ahed work form google calendar
    public function syncFromGoogle(Request $request)
    {
        try {
            $user = Auth::user();

            if (empty($user->google_access_token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Calendar not connected or token missing'
                ], 400);
            }

            // Check if token is expired BEFORE attempting sync
            if ($user->google_token_expires_at && Carbon::now()->isAfter($user->google_token_expires_at)) {
                Log::info('Token expired, will attempt refresh');
            }

            $token = json_decode($user->google_access_token, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($token)) {
                Log::error('Invalid token format', [
                    'json_error' => json_last_error_msg()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format. Please reconnect Google Calendar.'
                ], 400);
            }

            // CRITICAL: Ensure refresh token is in the token array
            if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                $token['refresh_token'] = $user->google_refresh_token;
            }

            if (!isset($token['refresh_token'])) {
                Log::error('No refresh token available', [
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh token missing. Please reconnect Google Calendar.'
                ], 400);
            }

            // Set access token and handle refresh
            $newToken = $this->googleCalendar->setAccessToken($token);

            // Update token if refreshed
            if ($newToken) {
                Log::info('Token was refreshed, updating database');

                $user->google_access_token = json_encode($newToken);

                // IMPORTANT: Keep refresh token
                if (isset($newToken['refresh_token'])) {
                    $user->google_refresh_token = $newToken['refresh_token'];
                } elseif ($user->google_refresh_token) {
                    // Preserve existing refresh token
                    $newToken['refresh_token'] = $user->google_refresh_token;
                }

                // Set proper expiry time
                if (isset($newToken['expires_in'])) {
                    $user->google_token_expires_at = Carbon::now()->addSeconds($newToken['expires_in']);
                }

                $user->save();
            }

            // Rest of your sync code...
            $startDate = $request->get('start')
                ? Carbon::parse($request->get('start'))
                : Carbon::now()->subMonths(3)->startOfMonth();

            $endDate = $request->get('end')
                ? Carbon::parse($request->get('end'))
                : Carbon::now()->addMonths(3)->endOfMonth();

            $events = $this->googleCalendar->listEvents($startDate, $endDate);

            $syncedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($events as $event) {
                try {
                    $googleEventId = $event->getId();

                    if ($event->getStatus() === 'cancelled') {
                        $skippedCount++;
                        continue;
                    }

                    $existingWork = Work::where('google_event_id', $googleEventId)
                        ->where('user_id', $user->id) // Add user_id check
                        ->first();

                    $isAllDay = false;
                    $startDateTime = null;
                    $endDateTime = null;

                    if ($event->getStart()->getDate()) {
                        $isAllDay = true;
                        $startDateTime = Carbon::parse($event->getStart()->getDate())->startOfDay();
                        $endDateTime = Carbon::parse($event->getEnd()->getDate())->subDay()->endOfDay();
                    } elseif ($event->getStart()->getDateTime()) {
                        $startDateTime = Carbon::parse($event->getStart()->getDateTime());
                        $endDateTime = Carbon::parse($event->getEnd()->getDateTime());
                    } else {
                        $skippedCount++;
                        continue;
                    }

                    $workData = [
                        'user_id' => $user->id, // Add user_id
                        'title' => $event->getSummary() ?? 'Untitled Event',
                        'description' => $event->getDescription(),
                        'location' => $event->getLocation(),
                        'start_datetime' => $startDateTime,
                        'end_datetime' => $endDateTime,
                        'is_all_day' => $isAllDay,
                        'google_event_id' => $googleEventId,
                        'google_synced_at' => Carbon::now(),
                    ];

                    if ($existingWork) {
                        $existingWork->update($workData);
                        $updatedCount++;
                    } else {
                        Work::create($workData);
                        $syncedCount++;
                    }
                } catch (Exception $e) {
                    Log::error('Error syncing individual event', [
                        'event_id' => $event->getId(),
                        'error' => $e->getMessage()
                    ]);
                    $skippedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced! New: {$syncedCount}, Updated: {$updatedCount}, Skipped: {$skippedCount}",
                'synced' => $syncedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount
            ]);
        } catch (Exception $e) {
            Log::error('Google Sync Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
