<?php

namespace App\Http\Controllers\Api\CMS;

use App\Models\CMS;
use App\Models\Slider;
use App\Models\Gallery;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\CMSResource;
use App\Http\Resources\CMS\SliderResource;

class CmsController extends Controller
{
    use ApiResponse;

    public function home()
    {
        $hero = CMS::where('page', 'home')
            ->where('section', 'hero')
            ->get();

        $sliders = Slider::where('status', true)
            ->orderBy('order', 'asc')
            ->get();

        $housingOptions = CMS::where('page', 'home')
            ->where('section', 'housing-options')
            ->where('name', 'item')
            ->get();

        $howItWorks = CMS::where('page', 'home')
            ->where('section', 'how-it-works')
            ->where('name', 'item')
            ->get();

        $howItWorksItem = CMS::where('page', 'home')
            ->where('section', 'how-it-works')
            ->where('name', 'card')
            ->get();

        $employeeAndSponsor = CMS::where('page', 'home')
            ->where('section', 'employee-and-sponsor')
            ->where('name', 'item')
            ->get();

        $primeLocation = CMS::where('page', 'home')
            ->where('section', 'prime-location')
            ->where('name', 'item')
            ->get();

        $appartment = CMS::where('page', 'home')
            ->where('section', 'apartment')
            ->where('name', 'item')
            ->get();

        $gallery = Gallery::get();

        $gallery = $gallery->map(function ($item) {
            return [
                'id' => $item->id,
                'image_path' => $item->image_path,
                'image_url' => asset($item->image_path) ?? null
            ];
        });

        return $this->success([
            'home' => [
                'hero' => CMSResource::collection($hero),
                'sliders' => SliderResource::collection($sliders),
                'housing_options' => CMSResource::collection($housingOptions),
                'how_it_works' => CMSResource::collection($howItWorks),
                'how_it_works_item' => CMSResource::collection($howItWorksItem),
                'employee_and_sponsor' => CMSResource::collection($employeeAndSponsor),
                'prime_location' => CMSResource::collection($primeLocation),
                'appartment' => CMSResource::collection($appartment),
                'gallery' => $gallery
            ]
        ], 'Home data retrieved successfully');
    }
}
