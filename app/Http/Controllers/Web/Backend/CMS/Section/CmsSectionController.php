<?php

namespace App\Http\Controllers\Web\Backend\CMS\Section;

use App\Models\CMS;
use App\Models\Slider;
use App\Models\Gallery;
use App\Models\PricingPlan;
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

                    // home page housing option section
                case 'housing-options':
                    $data = CMS::where('page', 'home')
                        ->where('section', 'housing-options')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.home.housing-option', compact('data'))->render();


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

                    // Property page - propert banner section
                case 'property-banner':
                    $data = CMS::where('page', 'properties')
                        ->where('section', 'hero')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.properties.properties-banner', compact('data'))->render();

                    // property page - our offer section
                case 'property-our-offer':
                    $data = CMS::where('page', 'properties')
                        ->where('section', 'our-offer')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.properties.our-offer', compact('data'))->render();

                    // property page - property one section
                case 'property-one':
                    $data = CMS::where('page', 'properties')
                        ->where('section', 'property-one')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.properties.property-one', compact('data'))->render();

                    // property page - property two section
                case 'property-two':
                    $data = CMS::where('page', 'properties')
                        ->where('section', 'property-two')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.properties.property-two', compact('data'))->render();

                    // property page - property three section
                case 'property-three':
                    $data = CMS::where('page', 'properties')
                        ->where('section', 'property-three')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.properties.property-three', compact('data'))->render();

                    // about page - about us breadcrumb section
                case 'about-us-breadcrumb':
                    $data = CMS::where('page', 'about')
                        ->where('section', 'about-us-breadcrumb')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.about.about-breadcrumb', compact('data'))->render();

                    // about page - contact us breadcrumb
                case 'about-contact-breadcrumb':
                    $data = CMS::where('page', 'about')
                        ->where('section', 'about-contact-breadcrumb')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.about.contact-breadcrumb', compact('data'))->render();

                    // amenities page - hero section
                case 'amenities-hero-section':
                    $data = CMS::where('page', 'amenities')
                        ->where('section', 'hero')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.amenities.amenities-hero', compact('data'))->render();

                    // amenities page - amenities feature section
                case 'amenities-feature':
                    // Check if this is a DataTable AJAX request
                    if ($request->ajax() && $request->has('draw')) {
                        $items = CMS::where('page', 'amenities')
                            ->where('section', 'amenities-feature')
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
                            ->addColumn('description', function ($row) {
                                if (!$row->description || $row->description === '---') {
                                    return '<span class="text-muted">No description</span>';
                                }

                                // Strip HTML tags and limit to 60 characters
                                $plainText = strip_tags($row->description);
                                $limited = \Illuminate\Support\Str::limit($plainText, 60);

                                // Add tooltip for full text
                                return '<span title="' . htmlspecialchars($plainText) . '">' . $limited . '</span>';
                            })
                            ->rawColumns(['image', 'action', 'description']) // Now add description
                            ->addColumn('action', function ($row) {
                                return '
                                    <button class="btn btn-sm btn-info edit-item"
                                        data-id="' . $row->id . '"
                                        data-title="' . htmlspecialchars($row->title) . '"
                                        data-description="' . htmlspecialchars($row->description) . '"
                                        data-image="' . ($row->image ?? '') . '">
                                        <i class="fe fe-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-feature-item" data-id="' . $row->id . '">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                ';
                            })
                            ->rawColumns(['image', 'action', 'description'])
                            ->make(true);
                    }

                    // Regular page load - return view
                    $data = CMS::where('page', 'amenities')
                        ->where('section', 'amenities-feature')
                        ->where('name', 'item')
                        ->first();

                    return view('backend.layouts.cms.amenities.amenities-feature', compact('data'))->render();

                    // Pricing page - pricing hero section
                case 'pricing-hero-section':
                    $data = CMS::where('page', 'pricing')
                        ->where('section', 'hero')
                        ->where('name', 'item')
                        ->first();
                    return view('backend.layouts.cms.pricing.pricing-banner', compact('data'))->render();

                    // Pricing page - pricing plans section
                case 'pricing-item':
                    // Check if this is a DataTable AJAX request
                    if ($request->ajax() && $request->has('draw')) {
                        $plans = PricingPlan::query()->orderBy('created_at', 'desc');

                        return DataTables::of($plans)
                            ->addIndexColumn()
                            ->addColumn('price', function ($row) {
                                return '$' . number_format($row->price_per_bed, 0) . ' / per bed';
                            })
                            ->addColumn('tenants', function ($row) {
                                return $row->tenants_per_room . ' tenants per room';
                            })
                            ->addColumn('amenities_list', function ($row) {
                                if (!$row->amenities || count($row->amenities) == 0) {
                                    return '<span class="badge bg-secondary">No amenities</span>';
                                }

                                $badges = '';
                                foreach ($row->amenities as $amenity) {
                                    $badges .= '<span class="badge bg-info me-1 mb-1">' . htmlspecialchars($amenity) . '</span>';
                                }
                                return $badges;
                            })
                            ->addColumn('status', function ($row) {
                                $checked = $row->is_active ? 'checked' : '';
                                return '
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-status" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                        </div>
                    ';
                            })
                            ->addColumn('action', function ($row) {
                                return '
                        <button class="btn btn-sm btn-info edit-plan me-1"
                            data-id="' . $row->id . '"
                            data-name="' . htmlspecialchars($row->name) . '"
                            data-price="' . $row->price_per_bed . '"
                            data-tenants="' . $row->tenants_per_room . '"
                            data-amenities=\'' . json_encode($row->amenities ?? []) . '\'
                            data-description="' . htmlspecialchars($row->description ?? '') . '">
                            <i class="fe fe-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-plan" data-id="' . $row->id . '">
                            <i class="fe fe-trash-2"></i>
                        </button>
                    ';
                            })
                            ->rawColumns(['amenities_list', 'status', 'action'])
                            ->make(true);
                    }

                    return view('backend.layouts.cms.pricing.pricing-item')->render();

                    // fallback for unknown sections
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
