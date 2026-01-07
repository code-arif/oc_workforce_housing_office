<?php

namespace App\Http\Controllers\Web\Backend\CMS\About;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class AboutPageController extends Controller
{
    /**
     * Update about us page about breadcrumb
     */
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'about')
                ->where('section', 'about-us-breadcrumb')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/about/about-breadcrumb');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'about';
            $validated_data['section'] = 'about-us-breadcrumb';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'about',
                    'section' => 'about-us-breadcrumb',
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
     * Update about us page contact breadcrumb
     */
    public function contactUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'about')
                ->where('section', 'about-contact-breadcrumb')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/about/contact-breadcrumb');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'about';
            $validated_data['section'] = 'about-contact-breadcrumb';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'about',
                    'section' => 'about-contact-breadcrumb',
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
