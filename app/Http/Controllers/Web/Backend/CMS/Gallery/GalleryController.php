<?php

namespace App\Http\Controllers\Web\Backend\CMS\Gallery;

use Exception;
use App\Helper\Helper;
use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GalleryController extends Controller
{
    /**
     * Update gallery image
     */
    public function store(Request $request)
    {
        $request->validate([
            'gallery' => 'required',
            'gallery.*' => 'required|image|mimes:jpeg,jpg,png,webp,avif|max:2048'
        ], [
            'gallery.required' => 'Please select at least one image',
            'gallery.*.image' => 'Each file must be an image',
            'gallery.*.mimes' => 'Images must be jpeg, jpg, png, webp or avif format',
            'gallery.*.max' => 'Each image must not exceed 2MB'
        ]);

        try {
            $uploaded_images = [];

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    // Upload image using Helper
                    $image_path = Helper::uploadImage($image, 'galleries');

                    // Save to database
                    $gallery = Gallery::create([
                        'image_path' => $image_path
                    ]);

                    $uploaded_images[] = [
                        'id' => $gallery->id,
                        'image_path' => $image_path,
                        'image_url' => asset($image_path)
                    ];
                }
            }

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => count($uploaded_images) . ' image(s) uploaded successfully!',
                    'images' => $uploaded_images
                ]);
            }

            return back()->with('t-success', 'Images uploaded successfully!');
        } catch (Exception $e) {
            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('t-error', 'Failed to upload: ' . $e->getMessage());
        }
    }

    /**
     * Delete gallery image
     */
    public function destroy(Request $request, $id)
    {
        try {
            $gallery = Gallery::findOrFail($id);

            // Delete image file
            if ($gallery->image_path) {
                Helper::deleteImage($gallery->image_path);
            }

            // Delete database record
            $gallery->delete();

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully!'
                ]);
            }

            return back()->with('t-success', 'Image deleted successfully!');
        } catch (Exception $e) {
            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('t-error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
