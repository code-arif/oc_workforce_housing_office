<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Exception;
use App\Models\Work;
use App\Helper\Helper;
use App\Models\WorkImage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\WorkResource;
use App\Http\Resources\MapWorkResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WorkDetailsResource;

class WorkController extends Controller
{
    use ApiResponse;
    // work list view
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Get the first team the user is assigned to
            $team = $user->teams()->first();

            if (!$team) {
                return response()->json([
                    'status' => false,
                    'message' => 'User is not assigned to any team',
                ], 404);
            }

            // Query works for the user's team
            $query = Work::where('team_id', $team->id)->latest('id');

            // Filter
            $filter = $request->query('filter');
            $today = Carbon::today();

            switch ($filter) {
                case 'previous_day_work':
                    $query->whereDate('start_datetime', '<', $today);
                    break;
                case 'current_day_work':
                    $query->whereDate('start_datetime', $today);
                    break;
                case 'see_next_2_day_work':
                case 'see_next_3_day_work':
                case 'see_next_4_day_work':
                case 'see_next_5_day_work':
                case 'see_next_6_day_work':
                    $days = (int)str_replace('next_', '', $filter);
                    $query->whereDate('start_datetime', '>', $today)
                        ->whereDate('start_datetime', '<=', $today->copy()->addDays($days));
                    break;
            }

            // Pagination
            $perPage = $request->query('per_page', 10);
            $works = $query->orderBy('start_datetime', 'asc')->paginate($perPage);

            // Team details
            $teamData = [
                'id' => $team->id,
                'name' => $team->name,
                'member_count' => $team->users()->count(), // this is new line
            ];

            return response()->json([
                'status' => true,
                'message' => 'Works fetched successfully',
                'team' => $teamData,
                'data' => WorkResource::collection($works),
                'pagination' => [
                    'total' => $works->total(),
                    'current_page' => $works->currentPage(),
                    'last_page' => $works->lastPage(),
                    'per_page' => $works->perPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    // work list in map
    public function mapView(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Get the first team the user is assigned to
            $team = $user->teams()->first();

            if (!$team) {
                return response()->json([
                    'status' => false,
                    'message' => 'User is not assigned to any team',
                ], 404);
            }

            // Query works for the user's team
            $query = Work::where('team_id', $team->id);

            // Filter
            $filter = $request->query('filter');
            $today = Carbon::today();

            switch ($filter) {
                case 'previous_day_work':
                    $query->whereDate('start_datetime', '<', $today);
                    break;
                case 'current_day_work':
                    $query->whereDate('start_datetime', $today);
                    break;
                case 'see_next_2_day_work':
                case 'see_next_3_day_work':
                case 'see_next_4_day_work':
                case 'see_next_5_day_work':
                case 'see_next_6_day_work':
                    $days = (int)str_replace('next_', '', $filter);
                    $query->whereDate('start_datetime', '>', $today)
                        ->whereDate('start_datetime', '<=', $today->copy()->addDays($days));
                    break;
            }

            $perPage = $request->query('per_page', 10);
            $works = $query->orderBy('start_datetime', 'asc')->paginate($perPage);

            // Fetch team details
            $team = $user->team ? [
                'id' => $team->id,
                'name' => $team->name,
                'member_count' => $team->users()->count(), // this is new line
            ] : null;

            return response()->json([
                'status' => true,
                'message' => 'Works fetched successfully',
                'team' => $team,
                'data' => MapWorkResource::collection($works),
                'pagination' => [
                    'total' => $works->total(),
                    'current_page' => $works->currentPage(),
                    'last_page' => $works->lastPage(),
                    'per_page' => $works->perPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    // work mark as complete
    public function completeWork(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $work = Work::find($id);
            if (!$work) {
                return response()->json([
                    'status' => false,
                    'message' => 'Work not found',
                ], 404);
            }

            // Already completed check
            if ($work->is_completed) {
                return response()->json([
                    'status' => false,
                    'message' => 'This work has already been completed.',
                ], 400);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'note' => 'nullable|string|max:500',
                'images.*' => 'nullable|image',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Update work
            $work->is_completed = true;
            $work->note = $request->note ?? $work->note;
            $work->save();

            // Initialize uploadedImages array
            $uploadedImages = [];

            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'work_images');
                    if ($imagePath) {
                        WorkImage::create([
                            'work_id' => $work->id,
                            'image_path' => $imagePath,
                        ]);

                        $uploadedImages[] = url($imagePath);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Work marked as completed successfully.',
                'data' => [
                    'id' => $work->id,
                    'title' => $work->title,
                    'is_completed' => $work->is_completed,
                    'note' => $work->note,
                    'images' => $uploadedImages,
                ],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    // work incomplete
    public function incompleteWork(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found', 404);
            }

            $work = Work::with('images')->find($id);
            if (!$work) {
                return $this->error([], 'Work not found', 404);
            }

            // Check if already incomplete
            if (!$work->is_completed) {
                return $this->error([], 'This work is already marked as incomplete.', 400);
            }

            // Delete related images (both DB + physical)
            if ($work->images && $work->images->count() > 0) {
                foreach ($work->images as $image) {
                    // Delete from storage (if exists)
                    if (file_exists(public_path($image->image_path))) {
                        @unlink(public_path($image->image_path));
                    }

                    // Delete DB record
                    $image->delete();
                }
            }

            // Update work to incomplete
            $work->is_completed = false;
            $work->note = null;
            $work->save();

            DB::commit();

            return $this->success([
                'id' => $work->id,
                'title' => $work->title,
                'is_completed' => $work->is_completed,
                'images_deleted' => true,
            ], 'Work marked as incomplete successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    // work details
    public function show($id)
    {
        try {
            $work = Work::with(['images', 'team', 'category'])->find($id);

            if (!$work) {
                return response()->json([
                    'status' => false,
                    'message' => 'Work not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => new WorkDetailsResource($work),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
