<?php

namespace App\Http\Controllers\Calendar;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Exception;

class CalendarCrudController extends Controller
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Get all calendars for the authenticated user
     */
    public function index()
    {
        $calendars = Calendar::forUser(Auth::id())
            ->with(['works' => function ($query) {
                $query->whereBetween('start_datetime', [
                    now()->subYear(),
                    now()->addMonths(3)
                ]);
            }])
            ->get()
            ->map(function ($calendar) {
                return [
                    'id' => $calendar->id,
                    'name' => $calendar->name,
                    'color' => $calendar->color,
                    'is_visible' => $calendar->is_visible,
                    'is_default' => $calendar->is_default,
                    'google_calendar_id' => $calendar->google_calendar_id,
                    'event_count' => $calendar->works->count(),
                    'last_synced_at' => $calendar->last_synced_at?->diffForHumans(),
                ];
            });

        return response()->json($calendars);
    }

    /**
     * Create a new calendar and sync to Google
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $googleCalendarId = null;

            // Create in Google Calendar if connected
            if ($user->google_access_token) {
                try {
                    $token = json_decode($user->google_access_token, true);

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $this->updateUserToken($user, $newToken);
                    }

                    // Create Google Calendar
                    $googleCalendar = $this->googleCalendar->createGoogleCalendar(
                        $request->name,
                        $request->description,
                        $request->color
                    );

                    $googleCalendarId = $googleCalendar->getId();

                    Log::info('Calendar created in Google', [
                        'google_calendar_id' => $googleCalendarId
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to create Google Calendar', [
                        'error' => $e->getMessage()
                    ]);
                    // Continue without Google sync
                }
            }

            // Create local calendar
            $calendar = Calendar::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'color' => $request->color,
                'description' => $request->description,
                'is_visible' => true,
                'is_default' => false,
                'google_calendar_id' => $googleCalendarId,
            ]);

            return response()->json([
                'success' => true,
                'message' => $googleCalendarId
                    ? 'Calendar created and synced to Google!'
                    : 'Calendar created successfully!',
                'calendar' => $calendar
            ], 201);
        } catch (Exception $e) {
            Log::error('Error creating calendar', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create calendar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update calendar and sync to Google
     */
    public function update(Request $request, Calendar $calendar)
    {
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
            'is_visible' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Update in Google Calendar if connected and has google_calendar_id
            if ($user->google_access_token && $calendar->google_calendar_id) {
                try {
                    $token = json_decode($user->google_access_token, true);

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $this->updateUserToken($user, $newToken);
                    }

                    // Update Google Calendar
                    $this->googleCalendar->updateGoogleCalendar(
                        $calendar->google_calendar_id,
                        $request->name ?? $calendar->name,
                        $request->description ?? $calendar->description,
                        $request->color ?? $calendar->color
                    );

                    Log::info('Calendar updated in Google', [
                        'google_calendar_id' => $calendar->google_calendar_id
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to update Google Calendar', [
                        'error' => $e->getMessage()
                    ]);
                    // Continue with local update
                }
            }

            // Update local calendar
            $calendar->update($request->only(['name', 'color', 'description', 'is_visible']));

            return response()->json([
                'success' => true,
                'message' => 'Calendar updated successfully!',
                'calendar' => $calendar
            ]);
        } catch (Exception $e) {
            Log::error('Error updating calendar', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update calendar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle calendar visibility
     */
    public function toggleVisibility(Calendar $calendar)
    {
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $calendar->update(['is_visible' => !$calendar->is_visible]);

        return response()->json([
            'success' => true,
            'is_visible' => $calendar->is_visible
        ]);
    }

    /**
     * Delete calendar and from Google
     */
    public function destroy(Calendar $calendar)
    {
        if ($calendar->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($calendar->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default calendar'
            ], 400);
        }

        try {
            $user = Auth::user();

            // Delete from Google Calendar if connected
            if ($user->google_access_token && $calendar->google_calendar_id) {
                try {
                    $token = json_decode($user->google_access_token, true);

                    if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                        $token['refresh_token'] = $user->google_refresh_token;
                    }

                    $newToken = $this->googleCalendar->setAccessToken($token);

                    if ($newToken) {
                        $this->updateUserToken($user, $newToken);
                    }

                    // Delete Google Calendar
                    $this->googleCalendar->deleteGoogleCalendar($calendar->google_calendar_id);

                    Log::info('Calendar deleted from Google', [
                        'google_calendar_id' => $calendar->google_calendar_id
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to delete Google Calendar', [
                        'error' => $e->getMessage()
                    ]);
                    // Continue with local deletion
                }
            }

            // Delete local calendar (cascade will delete events)
            $calendar->delete();

            return response()->json([
                'success' => true,
                'message' => 'Calendar deleted successfully!'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting calendar', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete calendar: ' . $e->getMessage()
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
            $user->google_token_expires_at = now()->addSeconds($newToken['expires_in']);
        }

        $user->save();
    }
}
