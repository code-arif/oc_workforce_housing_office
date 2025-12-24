<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;

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
}
