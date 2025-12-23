<?php

namespace App\Http\Controllers\Calendar;

use Exception;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;

class TrashController extends Controller
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Get all trashed works
     */
    public function index()
    {
        $trashedWorks = Work::onlyTrashed()
            ->where('user_id', Auth::id())
            ->with(['team', 'category', 'calendar'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json($trashedWorks);
    }

    /**
     * Restore work from trash
     */
    public function restore($id)
    {
        try {
            $work = Work::onlyTrashed()
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            $work->restore();

            return response()->json([
                'success' => true,
                'message' => 'Work restored successfully!',
                'data' => $work->load(['team', 'category', 'calendar'])
            ]);
        } catch (Exception $e) {
            Log::error('Error restoring work', [
                'work_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore work'
            ], 500);
        }
    }

    /**
     * Permanently delete work
     */
    public function forceDelete($id)
    {
        DB::beginTransaction();

        try {
            $work = Work::onlyTrashed()
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            $user = Auth::user();

            // Delete from Google Calendar if connected
            if ($user->google_access_token && $work->google_event_id) {
                try {
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
                        $user->google_access_token = json_encode($newToken);
                        if (isset($newToken['refresh_token'])) {
                            $user->google_refresh_token = $newToken['refresh_token'];
                        }
                        $user->save();
                    }

                    $calendar = $work->calendar;
                    $googleCalendarId = $calendar->google_calendar_id ?? 'primary';

                    $this->googleCalendar->deleteEvent($work->google_event_id, $googleCalendarId);

                    Log::info('Google Calendar event deleted', [
                        'work_id' => $work->id,
                        'google_event_id' => $work->google_event_id
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to delete from Google Calendar', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $work->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work permanently deleted!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error permanently deleting work', [
                'work_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete work permanently'
            ], 500);
        }
    }

    /**
     * Empty entire trash
     */
    public function emptyTrash()
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $trashedWorks = Work::onlyTrashed()
                ->where('user_id', $user->id)
                ->get();

            $deletedCount = 0;

            foreach ($trashedWorks as $work) {
                if ($user->google_access_token && $work->google_event_id) {
                    try {
                        $token = json_decode($user->google_access_token, true);

                        if (!isset($token['refresh_token']) && $user->google_refresh_token) {
                            $token['refresh_token'] = $user->google_refresh_token;
                        }

                        $newToken = $this->googleCalendar->setAccessToken($token);

                        if ($newToken) {
                            $user->google_access_token = json_encode($newToken);
                            if (isset($newToken['refresh_token'])) {
                                $user->google_refresh_token = $newToken['refresh_token'];
                            }
                            $user->save();
                        }

                        $calendar = $work->calendar;
                        $googleCalendarId = $calendar->google_calendar_id ?? 'primary';

                        $this->googleCalendar->deleteEvent($work->google_event_id, $googleCalendarId);
                    } catch (Exception $e) {
                        Log::error('Failed to delete event from Google', [
                            'work_id' => $work->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $work->forceDelete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Trash emptied! {$deletedCount} items deleted permanently."
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error emptying trash', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to empty trash'
            ], 500);
        }
    }
}
