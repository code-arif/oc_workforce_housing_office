<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HowItWorksController extends Controller
{
    /**
     * Update how it works section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'home')
                ->where('section', 'how-it-works')
                ->where('name', 'item')
                ->first();

            // Add additional data
            $validated_data['page'] = 'home';
            $validated_data['section'] = 'how-it-works';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'how-it-works',
                    'name' => 'item'
                ],
                $validated_data
            );

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Content updated successfully!'
                ]);
            }

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * Store new item
     */
    public function storeItem(CmsRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = Helper::uploadImage($request->file('image'), 'cms/home/how-it-works');
                $validatedData['image'] = $imagePath;
            }

            // Create new CMS item (no update logic)
            $heroItem = CMS::create([
                'page'    => 'home',
                'section' => 'how-it-works',
                'name'    => 'card',
            ] + $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully!',
                'data'    => $heroItem,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item. Please try again.',
            ], 500);
        }
    }

    /**
     * Update existing item
     */
    public function updateItem(CmsRequest $request)
    {
        $id = $request->id;
        try {
            $validated_data = $request->validated();

            $item = CMS::findOrFail($id);

            // Handle image update
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($item && $item->image) {
                    Helper::deleteImage($item->image);
                }

                // Store new image
                $image_path = Helper::uploadImage($request->file('image'), 'cms/home/how-it-works');
                $validated_data['image'] = $image_path;
            }

            // Update the item record
            $item->update($validated_data);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating the item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * delete item
     **/
    public function destroy(Request $request)
    {
        $id = $request->id;

        $item = CMS::find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // delete image
        if ($item && $item->image) {
            Helper::deleteImage($item->image);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
    }
}
