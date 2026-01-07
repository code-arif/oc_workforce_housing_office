<?php

namespace App\Http\Controllers\Web\Backend\CMS\Amenities;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AmenitiesPageController extends Controller
{
    /**
     * Update amenities page hero section
     */
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'amenities')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/amenities/hero');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'amenities';
            $validated_data['section'] = 'hero';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'amenities',
                    'section' => 'hero',
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
        }
    }

    /**
     * Update amenities page amenities feature header
     */
    public function headerUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // Add additional data
            $validated_data['page'] = 'amenities';
            $validated_data['section'] = 'amenities-feature';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'amenities',
                    'section' => 'amenities-feature',
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
                $imagePath = Helper::uploadImage($request->file('image'), 'cms/amenities/feature');
                $validatedData['image'] = $imagePath;
            }

            // Create new CMS item
            $heroItem = CMS::create([
                'page'    => 'amenities',
                'section' => 'amenities-feature',
                'name'    => 'card',
                'title'   => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'image'   => $validatedData['image'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully!',
                'data'    => $heroItem,
            ], 201);
        } catch (Exception $e) {
            Log::error('Store Item Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage(),
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
                $image_path = Helper::uploadImage($request->file('image'), 'cms/amenities/feature');
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
