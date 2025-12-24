<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use App\Helper\Helper;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class HomePageSliderController extends Controller
{
    // Store new slider
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
                'status' => 'nullable|boolean'
            ]);

            $data = [
                'title' => $request->title,
                'location' => $request->location,
                'status' => $request->has('status') ? true : false,
                'order' => Slider::max('order') + 1,
            ];

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $data['image'] = Helper::uploadImage($request->file('image'), 'sliders');
            }

            Slider::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Slider added successfully!'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Slider Store Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add slider: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update slider status
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|boolean'
            ]);

            $slider = Slider::findOrFail($id);
            $slider->status = $request->status;
            $slider->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slider not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Slider Status Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete slider
    public function destroy($id)
    {
        try {
            $slider = Slider::findOrFail($id);

            // Delete image file
            if ($slider->image) {
                Helper::deleteImage($slider->image);
            }

            $slider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slider deleted successfully!'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slider not found.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Slider Delete Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete slider: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update slider order
    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'orders' => 'required|array',
                'orders.*.id' => 'required|exists:sliders,id',
                'orders.*.position' => 'required|integer|min:1'
            ]);

            $orders = $request->orders;

            foreach ($orders as $order) {
                Slider::where('id', $order['id'])
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
            Log::error('Slider Order Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }
}

