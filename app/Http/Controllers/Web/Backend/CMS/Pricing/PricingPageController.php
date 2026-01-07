<?php

namespace App\Http\Controllers\Web\Backend\CMS\Pricing;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class PricingPageController extends Controller
{
    /**
     * Update pricing page hero section
     */
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'pricing')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/pricing/hero');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'pricing';
            $validated_data['section'] = 'hero';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'pricing',
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
}
