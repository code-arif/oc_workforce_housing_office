<?php

namespace App\Http\Controllers\Web\Backend\CMS\Property;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class PropertyPageController extends Controller
{
    /**
     * Update property page banner section
     */
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'hero')
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
            $validated_data['section'] = 'hero';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
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

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-one')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/properties/property-one');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'property-one';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'property-one',
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
     * Update property page property two section
     */
    public function updatePropertyTwo(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-one')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/properties/property-two');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'property-two';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'property-two',
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
     * Update property page property three section
     */
    public function updatePropertyThree(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'properties')
                ->where('section', 'property-three')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/properties/property-three');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'properties';
            $validated_data['section'] = 'property-three';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'properties',
                    'section' => 'property-three',
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
}
