<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use App\Models\Calendar;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Validator;

class EventManageGoogleController extends Controller
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string',
                'location'      => 'nullable|string',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'start_time'    => 'nullable|date_format:h:i A',
                'end_time'      => 'nullable|date_format:h:i A',
                'work_date'     => 'required|date',
                'is_all_day'    => 'nullable|boolean',
                'team_id'       => 'nullable|exists:teams,id',
                'category_id'   => 'nullable|exists:categories,id',
                'category_name' => 'nullable|string|max:255',
                'calendar_id'   => 'required|exists:calendars,id', // REQUIRED NOW
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // Handle Category
            $categoryId = $request->category_id;
            if (!$categoryId && $request->category_name) {
                $category = Category::create([
                    'name' => $request->category_name,
                ]);
                $categoryId = $category->id;
            }

            // Prepare DateTime fields
            $isAllDay = $request->is_all_day ?? false;

            if ($isAllDay) {
                $startDatetime = Carbon::parse($request->work_date)->startOfDay();
                $endDatetime = Carbon::parse($request->work_date)->endOfDay();
            } else {
                $startDatetime = Carbon::parse($request->work_date . ' ' . $request->start_time);
                $endDatetime = Carbon::parse($request->work_date . ' ' . $request->end_time);
            }

            // Save Work with calendar_id
            $work = Work::create([
                'user_id'         => auth()->id(),
                'calendar_id'     => $request->calendar_id, // FIXED: Now properly saving
                'title'           => $request->title,
                'description'     => $request->description,
                'location'        => $request->location,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'start_datetime'  => $startDatetime,
                'end_datetime'    => $endDatetime,
                'is_all_day'      => $isAllDay,
                'team_id'         => $request->team_id,
                'category_id'     => $categoryId,
            ]);

            // Google Calendar Sync
            $user = auth()->user();
            if ($user && $user->google_access_token) {
                try {
                    $token = json_decode($user->google_access_token, true);

                    if (!is_array($token)) {
                        Log::warning('Token format invalid');
                        goto skip_google_sync;
                    }

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $this->updateUserToken($user, $newToken);
                    }

                    // Get the calendar's google_calendar_id
                    $calendar = Calendar::find($request->calendar_id);
                    $googleCalendarId = $calendar->google_calendar_id ?? 'primary';

                    // CREATE EVENT IN GOOGLE CALENDAR
                    $googleEventId = $this->googleCalendar->createEvent($work, $googleCalendarId);

                    $work->update([
                        'google_event_id' => $googleEventId,
                        'google_synced_at' => Carbon::now(),
                    ]);

                    Log::info('Work synced to Google Calendar', [
                        'work_id' => $work->id,
                        'google_event_id' => $googleEventId,
                        'calendar_id' => $googleCalendarId
                    ]);
                } catch (Exception $e) {
                    Log::error('Google Calendar sync failed', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            skip_google_sync:

            DB::commit();

            $work->load(['team', 'category', 'calendar']);

            return response()->json([
                'status'  => true,
                'message' => 'Work created successfully!',
                'data'    => $work,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creating work', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create work: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Work $work)
    {
        $work->load(['team', 'category', 'calendar']);
        return response()->json($work);
    }

    public function update(Request $request, Work $work)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string',
                'location'      => 'nullable|string',
                'latitude'      => 'nullable|numeric|between:-90,90',
                'longitude'     => 'nullable|numeric|between:-180,180',
                'start_time'    => 'nullable|date_format:h:i A',
                'end_time'      => 'nullable|date_format:h:i A',
                'work_date'     => 'required|date',
                'is_all_day'    => 'nullable|boolean',
                'team_id'       => 'nullable|exists:teams,id',
                'category_id'   => 'nullable|exists:categories,id',
                'category_name' => 'nullable|string|max:255',
                'note'          => 'nullable|string',
                'calendar_id'   => 'nullable|exists:calendars,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $categoryId = $request->category_id;
            if (!$categoryId && $request->category_name) {
                $category = Category::firstOrCreate(
                    ['name' => $request->category_name]
                );
                $categoryId = $category->id;
            }

            $isAllDay = $request->is_all_day ?? false;

            if ($isAllDay) {
                $startDatetime = Carbon::parse($request->work_date)->startOfDay();
                $endDatetime = Carbon::parse($request->work_date)->endOfDay();
            } else {
                $startDatetime = Carbon::parse($request->work_date . ' ' . $request->start_time);
                $endDatetime = Carbon::parse($request->work_date . ' ' . $request->end_time);
            }

            $work->update([
                'title'           => $request->title,
                'description'     => $request->description,
                'location'        => $request->location,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'start_datetime'  => $startDatetime,
                'end_datetime'    => $endDatetime,
                'is_all_day'      => $isAllDay,
                'team_id'         => $request->team_id,
                'category_id'     => $categoryId,
                'note'            => $request->note,
                'calendar_id'     => $request->calendar_id ?? $work->calendar_id,
            ]);

            // Google Calendar Sync
            $user = auth()->user();
            if ($user && $user->google_access_token && $work->google_event_id) {
                try {
                    $token = json_decode($user->google_access_token, true);

                    if (!is_array($token)) {
                        goto skip_google_update;
                    }

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $this->updateUserToken($user, $newToken);
                    }

                    $calendar = Calendar::find($work->calendar_id);
                    $googleCalendarId = $calendar->google_calendar_id ?? 'primary';

                    $this->googleCalendar->updateEvent($work, $googleCalendarId);

                    $work->update([
                        'google_synced_at' => Carbon::now(),
                    ]);

                    Log::info('Work updated in Google Calendar', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);
                } catch (Exception $e) {
                    Log::error('Google Calendar update failed', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            skip_google_update:

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Work updated successfully!',
                'data'    => $work->load(['team', 'category', 'calendar']),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error updating work', [
                'work_id' => $work->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to update work: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Work $work)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Delete from Google Calendar first (if connected)
            if ($user->google_access_token && $work->google_event_id) {
                try {
                    Log::info('Attempting to delete Google Calendar event', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);

                    $token = json_decode($user->google_access_token, true);

                    if (!is_array($token)) {
                        $token = [
                            'access_token' => $user->google_access_token,
                            'refresh_token' => $user->google_refresh_token,
                        ];
                    }

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $this->updateUserToken($user, $newToken);
                    }

                    // FIXED: Get calendar's google_calendar_id
                    $calendar = $work->calendar;
                    $googleCalendarId = $calendar && $calendar->google_calendar_id
                        ? $calendar->google_calendar_id
                        : 'primary';

                    // Delete from Google Calendar with proper calendar ID
                    $this->googleCalendar->deleteEvent($work->google_event_id, $googleCalendarId);

                    Log::info('Google Calendar event deleted successfully', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id,
                        'calendar_id' => $googleCalendarId
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to delete from Google Calendar', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue with local deletion even if Google delete fails
                }
            }

            // Soft delete from local database
            $work->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work moved to trash!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting work', [
                'work_id' => $work->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete work: ' . $e->getMessage()
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
}
