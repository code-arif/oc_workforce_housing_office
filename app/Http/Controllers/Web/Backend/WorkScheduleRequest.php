<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\RescheduleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class WorkScheduleRequest extends Controller
{
    // get reschedule work list
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rescheduleRequest = RescheduleRequest::where('status', true)->with([
                'work:id,title',
                'team:id,name'
            ])->get();

            return DataTables::of($rescheduleRequest)
                ->addIndexColumn()

                // Title
                ->addColumn('title', function ($item) {
                    return strlen($item->work->title) > 15
                        ? substr($item->work->title, 0, 15) . '...'
                        : $item->work->title;
                })

                // Team
                ->addColumn('team', function ($item) {
                    $teamName = $item->team ? $item->team->name : 'No Team';
                    if (strlen($teamName) > 15) {
                        $teamName = substr($teamName, 0, 15) . '...';
                    }
                    return '<span class="badge bg-success">' . e($teamName) . '</span>';
                })

                // Note
                ->addColumn('note', function ($item) {
                    return $item->note
                        ? (strlen($item->note) > 25 ? substr($item->note, 0, 25) . '...' : $item->note)
                        : '---';
                })

                // Suggested Start Time
                ->addColumn('suggested_start_time', function ($item) {
                    if ($item->is_all_day) {
                        return '<span class="badge bg-info">All Day</span>';
                    }
                    return $item->start_datetime
                        ? date('h:i A', strtotime($item->start_datetime))
                        : '---';
                })

                // Suggested End Time
                ->addColumn('suggested_end_time', function ($item) {
                    if ($item->is_all_day) {
                        return '<span class="badge bg-info">All Day</span>';
                    }
                    return $item->end_datetime
                        ? date('h:i A', strtotime($item->end_datetime))
                        : '---';
                })

                // Suggested Date
                ->addColumn('suggested_date', function ($item) {
                    // Extract date from start_datetime
                    return $item->start_datetime
                        ? date('d M Y', strtotime($item->start_datetime))
                        : '---';
                })

                // Actions
                ->addColumn('action', function ($item) {
                    return '<div class="d-flex justify-content-start align-items-center gap-1">
                     <button type="button" class="btn btn-sm btn-success rescheduleBtn"
                         data-id="' . $item->id . '">
                         <i class="fas fa-clock-rotate-left"></i> Reschedule
                     </button>
                </div>';
                })

                ->rawColumns(['title', 'note', 'action', 'team', 'suggested_start_time', 'suggested_end_time'])
                ->make(true);
        }

        return view("backend.layouts.reschedule.index");
    }

    // edit reschedule work list
    public function edit($id)
    {
        try {
            $reschedule = RescheduleRequest::with('work')->find($id);
            if (!$reschedule) {
                return response()->json(['success' => false, 'message' => 'Work not found.'], 404);
            }

            return response()->json(['success' => true, 'data' => $reschedule]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch work. ' . $e->getMessage()]);
        }
    }

    // update reschedule work
    public function update(Request $request, $id)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            // Find the reschedule request
            $reschedule = RescheduleRequest::findOrFail($id);

            // Find the associated work
            $work = Work::find($reschedule->work_id);
            if (!$work) {
                return response()->json([
                    'status' => false,
                    'message' => 'Work not found!'
                ], 404);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'work_date' => 'required|date',
                'start_time' => $request->is_all_day ? 'nullable' : 'required|date_format:h:i A',
                'end_time' => $request->is_all_day ? 'nullable' : 'required|date_format:h:i A|after:start_time',
                'is_all_day' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prepare DateTime fields
            $workDate = $request->work_date;
            $isAllDay = $request->has('is_all_day') && $request->is_all_day == 'on';

            if ($isAllDay) {
                // All day event
                $startDatetime = Carbon::parse($workDate)->startOfDay();
                $endDatetime = Carbon::parse($workDate)->endOfDay();
            } else {
                // Specific time event
                $startDatetime = Carbon::parse($workDate . ' ' . $request->start_time);
                $endDatetime = Carbon::parse($workDate . ' ' . $request->end_time);
            }

            // Update reschedule request
            $reschedule->update([
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'is_all_day' => $isAllDay,
                'status' => false, // Mark as processed
            ]);

            // Update the work table
            $work->update([
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'is_all_day' => $isAllDay,
                'is_rescheduled' => true,
                'is_completed' => false,
            ]);

            // Google Calendar Sync (if work is already synced)
            $user = auth()->user();
            if ($user && $user->google_access_token && $work->google_event_id) {
                try {
                    $googleService = new GoogleCalendarService();

                    // Set access token
                    $token = [
                        'access_token' => $user->google_access_token,
                        'refresh_token' => $user->google_refresh_token,
                        'expires_in' => Carbon::parse($user->google_token_expires_at)->diffInSeconds(now()),
                    ];

                    $newToken = $googleService->setAccessToken($token);

                    // If token was refreshed, update user
                    if ($newToken) {
                        $user->update([
                            'google_access_token' => $newToken['access_token'],
                            'google_token_expires_at' => now()->addSeconds($newToken['expires_in']),
                        ]);
                    }

                    // Update event in Google Calendar
                    $googleEventId = $googleService->updateEvent($work);

                    // Update sync timestamp
                    $work->update([
                        'google_synced_at' => now(),
                    ]);

                    Log::info('Work rescheduled and synced to Google Calendar', [
                        'work_id' => $work->id,
                        'google_event_id' => $googleEventId,
                        'new_start' => $startDatetime,
                        'new_end' => $endDatetime
                    ]);
                } catch (Exception $e) {
                    // Don't fail the whole operation if Google sync fails
                    Log::error('Google Calendar sync failed during reschedule', [
                        'work_id' => $work->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Work rescheduled successfully!',
                'data' => $work
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to reschedule work: ' . $e->getMessage()
            ], 500);
        }
    }
}
