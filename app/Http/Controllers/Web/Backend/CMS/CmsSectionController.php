<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use App\Models\CMS;
use App\Models\Slider;
use App\Http\Controllers\Controller;

class CmsSectionController extends Controller
{
    /**
     * Main CMS page
     */
    public function index()
    {
        $heroData = CMS::where('page', 'home')
            ->where('section', 'hero')
            ->where('name', 'item')
            ->first();

        $sliders = Slider::orderBy('order')->get();

        return view('backend.layouts.cms.layout.cms_layout', compact('heroData', 'sliders'));
    }

    /**
     * Load section content (AJAX only)
     */
    public function section($section)
    {
        // Only handle AJAX requests
        if (!request()->ajax() && !request()->wantsJson()) {
            return redirect()->route('cms.index');
        }

        return $this->loadSectionContent($section);
    }

    /**
     * Load section content
     */
    private function loadSectionContent($section)
    {
        try {
            switch ($section) {
                // home page hero section
                case 'hero':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'hero')
                        ->where('name', 'item')
                        ->first();

                    $sliders = Slider::orderBy('order')->get();

                    return view('backend.layouts.cms.sections.hero', compact('data', 'sliders'))->render();

                    // home page how it works section
                case 'how-it-works':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'how-it-works')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.sections.how-it-works', compact('data'))->render();

                case 'about':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'about')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.sections.about', compact('data'))->render();

                case 'services':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'services')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.sections.services', compact('data'))->render();

                case 'testimonials':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'testimonials')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.sections.testimonials', compact('data'))->render();

                case 'contact':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'contact')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.sections.contact', compact('data'))->render();

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Section not found'
                    ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading section: ' . $e->getMessage()
            ], 500);
        }
    }
}
