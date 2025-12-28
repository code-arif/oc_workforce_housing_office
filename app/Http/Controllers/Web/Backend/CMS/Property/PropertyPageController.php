<?php

namespace App\Http\Controllers\Web\Backend\CMS\Property;

use Exception;
use App\Models\CMS;
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
}
