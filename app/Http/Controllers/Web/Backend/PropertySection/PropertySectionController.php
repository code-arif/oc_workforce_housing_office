<?php

namespace App\Http\Controllers\Web\Backend\PropertySection;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PropertySectionController extends Controller
{
    /**
     * Display the property management main page with default 'list' section
     */
    public function index()
    {
        return view('backend.layouts.properties.layout.property-layout');
    }

    /**
     * Load specific section via AJAX
     */
    public function section(Request $request, $section)
    {
        $sectionFile = 'backend.layouts.properties.sections.' . $section;

        // Validate that the section exists
        if (!view()->exists($sectionFile)) {
            return response()->json([
                'success' => false,
                'message' => "Section '{$section}' not found"
            ], 404);
        }

        // Return the section blade content
        return view($sectionFile);
    }
}
