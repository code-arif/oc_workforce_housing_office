<?php

namespace App\Http\Controllers\Web\Backend\CMS\Property;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class PropertyPageController extends Controller
{
    /**
     * Update property page banner one section
     */
    public function updatePropertyBannerOne(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-banner-one')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/properties/hero');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'property-banner-one';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'property-banner-one',
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
     */
    public function updatePropertyBannerTwo(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-banner-two')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/properties/hero');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'property-banner-two';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'property-banner-two',
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
     * Update property page banner three section
     */
    public function updatePropertyBannerThree(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-banner-three')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/properties/hero');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'property-banner-three';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'property-banner-three',
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
     * Update property page our offer section
     */
    public function updateOurOffer(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'our-offer')
                ->where('name', 'item')
                ->first();

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'our-offer';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'our-offer',
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
     * Update property page property one section
     */
    public function updatePropertyOne(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-one')
                ->where('name', 'item')
                ->first();

            // Existing metadata
            $metadata = [];
            if ($existing && $existing->metadata) {
                $metadata = is_array($existing->metadata)
                    ? $existing->metadata
                    : json_decode($existing->metadata, true) ?? [];
            }

            // ── Handle removed existing images ───────────────────────────
            $removedImages = $request->input('removed_images', []);
            $existingImages = $request->input('existing_images', []);

            if ($request->hasFile('images')) {
                // Full replace - delete all old
                if (!empty($metadata['images'])) {
                    foreach ($metadata['images'] as $old) {
                        Helper::deleteImage($old);
                    }
                }
                $paths = [];
                foreach ($request->file('images') as $img) {
                    $paths[] = Helper::uploadImage($img, 'cms/properties/property-one/gallery');
                }
                $metadata['images'] = $paths;
            } else {
                // Partial - keep existing minus removed
                $kept = array_filter($metadata['images'] ?? [], fn($img) => !in_array($img, $removedImages));
                foreach ($removedImages as $rm) {
                    Helper::deleteImage($rm);
                }
                $metadata['images'] = array_values($kept);
            }

            // ── Handle remove_video flag ──────────────────────────────────
            if ($request->input('remove_video') == '1') {
                if (!empty($metadata['video']) && file_exists(public_path($metadata['video']))) {
                    @unlink(public_path($metadata['video']));
                }
                $metadata['video'] = null;
            }

            // ── Video upload ──────────────────────────────────────────────
            if ($request->hasFile('video')) {
                // Delete old video
                if (!empty($metadata['video']) && file_exists(public_path($metadata['video']))) {
                    @unlink(public_path($metadata['video']));
                }

                $videoFile = $request->file('video');
                $videoName = time() . '_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
                $videoPath = 'uploads/cms/properties/property-one/video';
                $videoFile->move(public_path($videoPath), $videoName);
                $metadata['video'] = $videoPath . '/' . $videoName;
            }

            $validated_data['metadata'] = $metadata;
            $validated_data['page']     = 'properties';
            $validated_data['section']  = 'property-one';
            $validated_data['name']     = 'item';

            CMS::updateOrCreate(
                ['page' => 'properties', 'section' => 'property-one', 'name' => 'item'],
                $validated_data
            );

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Content updated successfully!']);
            }

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
            }
        }
    }

    /**
     * Update property page property two section
     */
    public function updatePropertyTwo(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-two')
                ->where('name', 'item')
                ->first();

            $metadata = [];
            if ($existing && $existing->metadata) {
                $metadata = is_array($existing->metadata)
                    ? $existing->metadata
                    : json_decode($existing->metadata, true) ?? [];
            }

            // ── Gallery images ────────────────────────────────────────────
            $removedImages = $request->input('removed_images', []);

            if ($request->hasFile('images')) {
                if (!empty($metadata['images'])) {
                    foreach ($metadata['images'] as $old) {
                        Helper::deleteImage($old);
                    }
                }
                $paths = [];
                foreach ($request->file('images') as $img) {
                    $paths[] = Helper::uploadImage($img, 'cms/properties/property-two/gallery');
                }
                $metadata['images'] = $paths;
            } else {
                $kept = array_filter(
                    $metadata['images'] ?? [],
                    fn($img) => !in_array($img, $removedImages)
                );
                foreach ($removedImages as $rm) {
                    Helper::deleteImage($rm);
                }
                $metadata['images'] = array_values($kept);
            }

            // ── Remove video flag ─────────────────────────────────────────
            if ($request->input('remove_video') == '1') {
                if (!empty($metadata['video']) && file_exists(public_path($metadata['video']))) {
                    @unlink(public_path($metadata['video']));
                }
                $metadata['video'] = null;
            }

            // ── Video upload ──────────────────────────────────────────────
            if ($request->hasFile('video')) {
                if (!empty($metadata['video']) && file_exists(public_path($metadata['video']))) {
                    @unlink(public_path($metadata['video']));
                }

                $videoFile = $request->file('video');
                $videoName = time() . '_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
                $videoPath = 'uploads/cms/properties/property-two/video';
                $videoFile->move(public_path($videoPath), $videoName);
                $metadata['video'] = $videoPath . '/' . $videoName;
            }

            $validated_data['metadata'] = $metadata;
            $validated_data['page']     = 'properties';
            $validated_data['section']  = 'property-two';
            $validated_data['name']     = 'item';

            CMS::updateOrCreate(
                ['page' => 'properties', 'section' => 'property-two', 'name' => 'item'],
                $validated_data
            );

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Content updated successfully!']);
            }

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
            }
        }
    }

    /**
     * Update property page property three section
     */
    public function updatePropertyThree(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-three')
                ->where('name', 'item')
                ->first();

            $metadata = [];
            if ($existing && $existing->metadata) {
                $metadata = is_array($existing->metadata)
                    ? $existing->metadata
                    : json_decode($existing->metadata, true) ?? [];
            }

            // ── Gallery images ────────────────────────────────────────────
            $removedImages = $request->input('removed_images', []);

            if ($request->hasFile('images')) {
                if (!empty($metadata['images'])) {
                    foreach ($metadata['images'] as $old) {
                        Helper::deleteImage($old);
                    }
                }
                $paths = [];
                foreach ($request->file('images') as $img) {
                    $paths[] = Helper::uploadImage($img, 'cms/properties/property-three/gallery');
                }
                $metadata['images'] = $paths;
            } else {
                $kept = array_filter(
                    $metadata['images'] ?? [],
                    fn($img) => !in_array($img, $removedImages)
                );
                foreach ($removedImages as $rm) {
                    Helper::deleteImage($rm);
                }
                $metadata['images'] = array_values($kept);
            }

            // ── Remove video flag ─────────────────────────────────────────
            if ($request->input('remove_video') == '1') {
                if (!empty($metadata['video']) && file_exists(public_path($metadata['video']))) {
                    @unlink(public_path($metadata['video']));
                }
                $metadata['video'] = null;
            }

            // ── Video upload ──────────────────────────────────────────────
            if ($request->hasFile('video')) {
                if (!empty($metadata['video']) && file_exists(public_path($metadata['video']))) {
                    @unlink(public_path($metadata['video']));
                }

                $videoFile = $request->file('video');
                $videoName = time() . '_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
                $videoPath = 'uploads/cms/properties/property-three/video';
                $videoFile->move(public_path($videoPath), $videoName);
                $metadata['video'] = $videoPath . '/' . $videoName;
            }

            $validated_data['metadata'] = $metadata;
            $validated_data['page']     = 'properties';
            $validated_data['section']  = 'property-three';
            $validated_data['name']     = 'item';

            CMS::updateOrCreate(
                ['page' => 'properties', 'section' => 'property-three', 'name' => 'item'],
                $validated_data
            );

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Content updated successfully!']);
            }

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
            }
        }
    }
}
