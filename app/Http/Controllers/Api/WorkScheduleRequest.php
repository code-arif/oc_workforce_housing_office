<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Work;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\RescheduleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Validator;

class WorkScheduleRequest extends Controller
{
    use ApiResponse;

    // Store Reschedule Request
    public function upsert(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $validator = Validator::make($request->all(), [
                'work_id'       => 'required|exists:works,id',
                'work_date'     => 'nullable|date',
                'start_time'    => 'nullable|date_format:h:i A',
                'end_time'      => 'nullable|date_format:h:i A',
                'is_all_day'    => 'nullable|boolean',
                'note'          => 'nullable|max:250',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $message = $errors[0] ?? 'Validation failed.';
                if (count($errors) > 1) {
                    $message .= ' (and ' . (count($errors) - 1) . ' more errors)';
                }
                return $this->error([], $message, 422);
            }

            $work = Work::find($request->work_id);
            if (!$work) {
                return $this->error([], 'Work not found.', 404);
            }

            // Check if there's a pending request
            $existingRequest = RescheduleRequest::where('work_id', $work->id)
                ->where('status', 1)
                ->first();

            // Don't allow if request is already accepted/rejected
            if ($existingRequest && $existingRequest->status != 1) {
                return $this->error([], 'This request has already been processed and cannot be modified.', 400);
            }

            $isAllDay = $request->is_all_day ?? false;
            $startDatetime = null;
            $endDatetime = null;

            if ($request->work_date) {
                if ($isAllDay) {
                    $startDatetime = Carbon::parse($request->work_date)->startOfDay();
                    $endDatetime = Carbon::parse($request->work_date)->endOfDay();
                } else {
                    if ($request->start_time && $request->end_time) {
                        $startDatetime = Carbon::parse($request->work_date . ' ' . $request->start_time);
                        $endDatetime = Carbon::parse($request->work_date . ' ' . $request->end_time);
                    }
                }
            }

            // UpdateOrCreate - will update if exists, create if not
            $reschedule = RescheduleRequest::updateOrCreate(
                [
                    'work_id' => $work->id,
                    'status'  => 1, // Only target pending requests
                ],
                [
                    'team_id'        => $work->team_id,
                    'start_datetime' => $startDatetime,
                    'end_datetime'   => $endDatetime,
                    'is_all_day'     => $isAllDay,
                    'note'           => $request->note,
                ]
            );

            $message = $reschedule->wasRecentlyCreated
                ? 'Reschedule request created successfully!'
                : 'Reschedule request updated successfully!';

            return $this->success($reschedule, $message, 200);
        } catch (Exception $e) {
            Log::channel('single')->error('Reschedule upsert error', [
                'user_id' => auth('api')->id(),
                'work_id' => $request->work_id ?? null,
                'error' => $e->getMessage()
            ]);
            return $this->error([], $e->getMessage(), 500);
        }
    }

    // Edit Reschedule Request (Get single request for editing)
    public function edit($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $reschedule = RescheduleRequest::with(['work', 'team'])
                ->where('id', $id)
                ->first();

            if (!$reschedule) {
                return $this->error([], 'Reschedule request not found.', 404);
            }

            // Check if request is still pending
            if ($reschedule->status != 1) {
                return $this->error([], 'Only pending requests can be edited.', 400);
            }

            $data = [
                'id' => $reschedule->id,
                'work_id' => $reschedule->work_id,
                'work_title' => $reschedule->work->title,
                'team_name' => $reschedule->team->name ?? 'N/A',
                'current_start' => $reschedule->work->start_datetime,
                'current_end' => $reschedule->work->end_datetime,
                'suggested_start' => $reschedule->start_datetime,
                'suggested_end' => $reschedule->end_datetime,
                'is_all_day' => $reschedule->is_all_day,
                'note' => $reschedule->note,
                'status' => $reschedule->status,
                'created_at' => $reschedule->created_at->format('Y-m-d H:i:s'),
            ];

            return $this->success($data, 'Reschedule request retrieved successfully.', 200);
        } catch (Exception $e) {
            Log::channel('single')->error('Reschedule edit error', [
                'reschedule_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->error([], $e->getMessage(), 500);
        }
    }

    // Delete Reschedule Request
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $reschedule = RescheduleRequest::find($id);
            if (!$reschedule) {
                return $this->error([], 'Reschedule request not found.', 404);
            }

            // Check if request is still pending
            if ($reschedule->status != 1) {
                return $this->error([], 'Only pending requests can be deleted.', 400);
            }

            $reschedule->delete();

            return $this->success([], 'Reschedule request deleted successfully!', 200);
        } catch (Exception $e) {
            Log::channel('single')->error('Reschedule delete error', [
                'reschedule_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->error([], $e->getMessage(), 500);
        }
    }


    // Self Reschedule (User reschedules their own work - Database only, NO Google Calendar sync)
    public function selfReschedule(Request $request, $workId)
    {
        DB::beginTransaction();
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $work = Work::find($workId);
            if (!$work) {
                return $this->error([], 'Work not found.', 404);
            }

            $validator = Validator::make($request->all(), [
                'work_date'     => 'required|date',
                'start_time'    => 'required_if:is_all_day,false|date_format:h:i A',
                'end_time'      => 'required_if:is_all_day,false|date_format:h:i A',
                'is_all_day'    => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $message = $errors[0] ?? 'Validation failed.';
                if (count($errors) > 1) {
                    $message .= ' (and ' . (count($errors) - 1) . ' more errors)';
                }
                return $this->error([], $message, 422);
            }

            $isAllDay = $request->is_all_day ?? false;

            if ($isAllDay) {
                $startDatetime = Carbon::parse($request->work_date)->startOfDay();
                $endDatetime = Carbon::parse($request->work_date)->endOfDay();
            } else {
                $startDatetime = Carbon::parse($request->work_date . ' ' . $request->start_time);
                $endDatetime = Carbon::parse($request->work_date . ' ' . $request->end_time);
            }

            // Update work in database only (NO Google Calendar sync)
            $work->update([
                'start_datetime' => $startDatetime,
                'end_datetime'   => $endDatetime,
                'is_all_day'     => $isAllDay,
                'is_rescheduled' => true,
                'is_completed'   => false,
            ]);

            Log::info('Work self-rescheduled (database only)', [
                'work_id' => $work->id,
                'user_id' => $user->id,
                'new_start' => $startDatetime,
                'new_end' => $endDatetime
            ]);

            DB::commit();

            return $this->success($work, 'Work rescheduled successfully! Note: Google Calendar will be updated when admin syncs.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Self reschedule error', [
                'work_id' => $workId,
                'user_id' => auth('api')->id(),
                'error' => $e->getMessage()
            ]);

            return $this->error([], $e->getMessage(), 500);
        }
    }
}
