<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;

class BiDirectionalSyncController extends Controller
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Full bi-directional sync with deletion detection - NO DUPLICATES
     */
    public function fullSync(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if (empty($user->google_access_token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Calendar not connected'
                ], 400);
            }

            $token = json_decode($user->google_access_token, true);

            if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                $token['refresh_token'] = $user->google_refresh_token;
            }

            $newToken = $this->googleCalendar->setAccessToken($token);

            if ($newToken) {
                $this->updateUserToken($user, $newToken);
            }

            $googleCalendarsList = $this->googleCalendar->listAllCalendars();

            $startDate = Carbon::now()->subYear()->startOfDay();
            $endDate = Carbon::now()->addMonths(3)->endOfDay();

            $googleEventIds = [];
            $syncedCount = 0;
            $updatedCount = 0;
            $deletedCount = 0;

            foreach ($googleCalendarsList as $googleCal) {
                try {
                    $calendarId = $googleCal->getId();
                    $calendarName = $googleCal->getSummary();
                    $calendarColor = $this->getCalendarColor($googleCal);

                    // Create or update local calendar
                    $localCalendar = Calendar::updateOrCreate(
                        [
                            'google_calendar_id' => $calendarId,
                            'user_id' => $user->id
                        ],
                        [
                            'name' => $calendarName,
                            'color' => $calendarColor,
                            'is_visible' => true,
                            'last_synced_at' => now(),
                        ]
                    );

                    $events = $this->googleCalendar->listEventsForCalendar(
                        $calendarId,
                        $startDate,
                        $endDate
                    );

                    foreach ($events as $event) {
                        try {
                            $googleEventId = $event->getId();

                            // Handle cancelled events
                            if ($event->getStatus() === 'cancelled') {
                                $deletedWork = Work::where('google_event_id', $googleEventId)
                                    ->where('user_id', $user->id)
                                    ->first();

                                if ($deletedWork) {
                                    $deletedWork->forceDelete(); // Permanently delete
                                    $deletedCount++;
                                }
                                continue;
                            }

                            $googleEventIds[] = $googleEventId;

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
                                continue;
                            }

                            $workData = [
                                'user_id' => $user->id,
                                'calendar_id' => $localCalendar->id,
                                'title' => $event->getSummary() ?? 'Untitled Event',
                                'description' => $event->getDescription(),
                                'location' => $event->getLocation(),
                                'start_datetime' => $startDateTime,
                                'end_datetime' => $endDateTime,
                                'is_all_day' => $isAllDay,
                                'google_event_id' => $googleEventId,
                                'google_synced_at' => Carbon::now(),
                            ];

                            // FIXED: Check by google_event_id AND user_id to prevent duplicates
                            $existingWork = Work::withTrashed()
                                ->where('google_event_id', $googleEventId)
                                ->where('user_id', $user->id)
                                ->first();

                            if ($existingWork) {
                                // Restore if soft deleted
                                if ($existingWork->trashed()) {
                                    $existingWork->restore();
                                }
                                $existingWork->update($workData);
                                $updatedCount++;
                            } else {
                                Work::create($workData);
                                $syncedCount++;
                            }
                        } catch (Exception $e) {
                            Log::error('Error syncing event', [
                                'event_id' => $event->getId(),
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error syncing calendar', [
                        'calendar_id' => $googleCal->getId(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Delete orphaned events (events that don't exist in Google anymore)
            $orphanedWorks = Work::where('user_id', $user->id)
                ->whereNotNull('google_event_id')
                ->whereNotIn('google_event_id', $googleEventIds)
                ->whereBetween('start_datetime', [$startDate, $endDate])
                ->get();

            foreach ($orphanedWorks as $work) {
                $work->forceDelete(); // Permanently delete
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Full sync completed!",
                'stats' => [
                    'new_events' => $syncedCount,
                    'updated_events' => $updatedCount,
                    'deleted_events' => $deletedCount,
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Bi-Directional Sync Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateUserToken($user, $newToken)
    {
        $user->google_access_token = json_encode($newToken);

        if (isset($newToken['refresh_token'])) {
            $user->google_refresh_token = $newToken['refresh_token'];
        }

        if (isset($newToken['expires_in'])) {
            $user->google_token_expires_at = Carbon::now()->addSeconds($newToken['expires_in']);
        }

        $user->save();
    }

    private function getCalendarColor($googleCalendar)
    {
        $colorId = $googleCalendar->getColorId();

        $colors = [
            '1' => '#7986cb',
            '2' => '#33b679',
            '3' => '#8e24aa',
            '4' => '#e67c73',
            '5' => '#f6bf26',
            '6' => '#f4511e',
            '7' => '#039be5',
            '8' => '#616161',
            '9' => '#3f51b5',
            '10' => '#0b8043',
            '11' => '#d50000',
        ];

        return $colors[$colorId] ?? '#3b82f6';
    }
}
