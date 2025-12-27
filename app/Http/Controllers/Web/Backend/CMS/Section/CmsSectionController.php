<?php

namespace App\Http\Controllers\Web\Backend\CMS\Section;

use App\Models\CMS;
use App\Models\Slider;
use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

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
    public function section(Request $request, $section)
    {
        // Only handle AJAX requests
        if (!$request->ajax() && !$request->wantsJson()) {
            return redirect()->route('cms.index');
        }

        return $this->loadSectionContent($section, $request);
    }

    /**
     * Load section content
     */
    private function loadSectionContent($section, Request $request)
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

                    return view('backend.layouts.cms.home.hero', compact('data', 'sliders'))->render();

                    // home page how it works section
                case 'how-it-works':
                    // Check if this is a DataTable AJAX request
                    if ($request->ajax() && $request->has('draw')) {
                        $items = CMS::where('page', 'home')
                            ->where('section', 'how-it-works')
                            ->where('name', 'card')
                            ->orderBy('created_at', 'desc');

                        return DataTables::of($items)
                            ->addIndexColumn()
                            ->addColumn('image', function ($row) {
                                if ($row->image) {
                                    return '<img src="' . asset($row->image) . '" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">';
                                }
                                return '<span class="badge bg-secondary">No Image</span>';
                            })
                            ->addColumn('title', fn($row) => $row->title ?? '---')
                            ->addColumn('sub_title', fn($row) => $row->sub_title ?? '---')
                            ->addColumn('action', function ($row) {
                                return '
                                    <button class="btn btn-sm btn-info edit-item"
                                        data-id="' . $row->id . '"
                                        data-title="' . htmlspecialchars($row->title) . '"
                                        data-subtitle="' . htmlspecialchars($row->sub_title) . '"
                                        data-image="' . ($row->image ?? '') . '">
                                        <i class="fe fe-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-item" data-id="' . $row->id . '">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                ';
                            })
                            ->rawColumns(['image', 'action'])
                            ->make(true);
                    }

                    // Regular page load - return view
                    $data = CMS::where('page', 'home')
                        ->where('section', 'how-it-works')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.home.how-it-works', compact('data'))->render();

                    // home page - employee-and-sponsor section
                case 'employee-and-sponsor':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'employee-and-sponsor')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.home.employee-and-sponsor', compact('data'))->render();

                    // home page - prime-location section
                case 'prime-location':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'prime-location')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.home.prime-location', compact('data'))->render();

                    // home page - apartment section
                case 'apartment':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'apartment')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.home.apartment', compact('data'))->render();

                    // home page - gallery section
                case 'gallery':
                    $galleries = Gallery::latest()->get();
                    return view('backend.layouts.cms.home.gallery', compact('galleries'))->render();

                    // about page - about us breadcrumb section
                case 'about-us-breadcrumb':
                    $data = CMS::where('page', 'about')
                        ->where('section', 'about-us-breadcrumb')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.about.about-breadcrumb', compact('data'))->render();
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
