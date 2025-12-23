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

class SyncMultipleGoogleCalendarsController extends Controller
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Sync all Google Calendars for the user (last 1 year data)
     */
    public function syncAll(Request $request)
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

            // Set access token and handle refresh
            $newToken = $this->googleCalendar->setAccessToken($token);

            if ($newToken) {
                $this->updateUserToken($user, $newToken);
            }

            // Fetch all Google Calendars
            $googleCalendarsList = $this->googleCalendar->listAllCalendars();

            $syncedCalendars = 0;
            $totalEvents = 0;
            $newEvents = 0;
            $updatedEvents = 0;

            // Date range: last 1 year to 3 months ahead
            $startDate = Carbon::now()->subYear()->startOfDay();
            $endDate = Carbon::now()->addMonths(3)->endOfDay();

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

                    // Fetch events for this calendar
                    $events = $this->googleCalendar->listEventsForCalendar(
                        $calendarId,
                        $startDate,
                        $endDate
                    );

                    foreach ($events as $event) {
                        try {
                            if ($event->getStatus() === 'cancelled') {
                                continue;
                            }

                            $googleEventId = $event->getId();
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

                            $existingWork = Work::where('google_event_id', $googleEventId)
                                ->where('user_id', $user->id)
                                ->first();

                            if ($existingWork) {
                                $existingWork->update($workData);
                                $updatedEvents++;
                            } else {
                                Work::create($workData);
                                $newEvents++;
                            }

                            $totalEvents++;
                        } catch (Exception $e) {
                            Log::error('Error syncing event', [
                                'event_id' => $event->getId(),
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    $syncedCalendars++;
                } catch (Exception $e) {
                    Log::error('Error syncing calendar', [
                        'calendar_id' => $googleCal->getId(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedCalendars} calendars with {$totalEvents} total events!",
                'stats' => [
                    'calendars' => $syncedCalendars,
                    'total_events' => $totalEvents,
                    'new_events' => $newEvents,
                    'updated_events' => $updatedEvents,
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Multi-Calendar Sync Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user token after refresh
     */
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

    /**
     * Extract color from Google Calendar
     */
    private function getCalendarColor($googleCalendar)
    {
        $colorId = $googleCalendar->getColorId();

        // Google Calendar default colors
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
