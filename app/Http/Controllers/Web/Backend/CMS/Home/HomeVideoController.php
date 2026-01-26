<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use App\Helper\Helper;
use App\Models\HomeVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HomeVideoController extends Controller
{
    /**
     * Store new video
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'video' => 'required|file|mimes:mp4,avi,mov,wmv|max:51200', // 50MB max
                'key_points' => 'nullable|array',
                'key_points.*' => 'nullable|string|max:255',
                'status' => 'nullable|boolean'
            ]);

            $data = [
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'key_points' => array_filter($request->key_points ?? []), // Remove empty values
                'status' => $request->has('status') ? true : false,
                'order' => HomeVideo::max('order') + 1,
            ];

            // Handle Video Upload
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $filename = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
                $path = $video->storeAs('videos', $filename, 'public');
                $data['video_url'] = 'storage/' . $path;
            }

            HomeVideo::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Video added successfully!'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Video Store Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing video
     */
    public function update(Request $request, $id)
    {

    // dd($request->all());
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200', // 50MB max
                'key_points' => 'nullable|array',
                'key_points.*' => 'nullable|string|max:255',
                'status' => 'nullable|boolean'
            ]);

            $video = HomeVideo::findOrFail($id);

            $data = [
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'key_points' => array_filter($request->key_points ?? []),
                'status' => $request->has('status') ? true : false,
            ];

            // Handle Video Upload
            if ($request->hasFile('video')) {
                // Delete old video
                if ($video->video_url && Storage::disk('public')->exists(str_replace('storage/', '', $video->video_url))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $video->video_url));
                }

                $videoFile = $request->file('video');
                $filename = time() . '_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
                $path = $videoFile->storeAs('videos', $filename, 'public');
                $data['video_url'] = 'storage/' . $path;
            }

            $video->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Video updated successfully!'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found.'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Video Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update video status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|boolean'
            ]);

            $video = HomeVideo::findOrFail($id);
            $video->status = $request->status;
            $video->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Video Status Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete video
     */
    public function destroy($id)
    {
        try {
            $video = HomeVideo::findOrFail($id);

            // Delete video file
            if ($video->video_url && Storage::disk('public')->exists(str_replace('storage/', '', $video->video_url))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $video->video_url));
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully!'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Video Delete Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update video order
     */
    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'orders' => 'required|array',
                'orders.*.id' => 'required|exists:home_videos,id',
                'orders.*.position' => 'required|integer|min:1'
            ]);

            $orders = $request->orders;

            foreach ($orders as $order) {
                HomeVideo::where('id', $order['id'])
                    ->update(['order' => $order['position']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Video Order Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }
}
