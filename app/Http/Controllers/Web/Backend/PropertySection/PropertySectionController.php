<?php

namespace App\Http\Controllers\Web\Backend\PropertySection;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PropertySectionController extends Controller
{
    public function __construct()
    {
        // You can add middleware here for permissions if needed
        $this->middleware('permission:property.list')->only('index', 'section');
        $this->middleware('permission:property-type.list')->only('index', 'section');
        $this->middleware('permission:units.list')->only('index', 'section');
        $this->middleware('permission:rooms.list')->only('index', 'section');
        $this->middleware('permission:beds.list')->only('index', 'section');

    }
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
