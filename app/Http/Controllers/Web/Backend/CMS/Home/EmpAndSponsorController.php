<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

class EmpAndSponsorController extends Controller
{
    /**
     * Update employee and sponsor section data
     */
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'employee-and-sponsor')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/home/employee-and-sponsor');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'home';
            $validated_data['section'] = 'employee-and-sponsor';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'employee-and-sponsor',
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
