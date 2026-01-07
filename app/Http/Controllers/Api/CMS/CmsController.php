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

    /**
     * CMS Home page data
     */
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
        ], 'Home page data retrieved successfully');
    }

    /**
     * CMS Properties page data
     */
    public function properties()
    {
        $hero = CMS::where('page', 'properties')
            ->where('section', 'hero')
            ->where('name', 'item')
            ->get();

        $ourOffer = CMS::where('page', 'properties')
            ->where('section', 'our-offer')
            ->where('name', 'item')
            ->get();

        $propertyOne = CMS::where('page', 'properties')
            ->where('section', 'property-one')
            ->where('name', 'item')
            ->get();

        $propertyTwo = CMS::where('page', 'properties')
            ->where('section', 'property-two')
            ->where('name', 'item')
            ->get();

        $propertyThree = CMS::where('page', 'properties')
            ->where('section', 'property-three')
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
            'properties' => [
                'hero' => CMSResource::collection($hero),
                'our_offer' => CMSResource::collection($ourOffer),
                'property_one' => CMSResource::collection($propertyOne),
                'gallery' => $gallery,
                'property_two' => CMSResource::collection($propertyTwo),
                'property_three' => CMSResource::collection($propertyThree),
            ]
        ], 'Properties page data retrieved successfully');
    }

    /**
     * CMS About us page data
     */
    public function aboutUs()
    {
        $aboutUsBreadcrumb = CMS::where('page', 'about')
            ->where('section', 'about-us-breadcrumb')
            ->where('name', 'item')
            ->get();

        $aboutContactBreadcrumb = CMS::where('page', 'about')
            ->where('section', 'about-contact-breadcrumb')
            ->where('name', 'item')
            ->get();

        $gallery = Gallery::limit(2)->get();

        $gallery = $gallery->map(function ($item) {
            return [
                'id' => $item->id,
                'image_path' => $item->image_path,
                'image_url' => asset($item->image_path) ?? null
            ];
        });

        return $this->success([
            'about-us' => [
                'about-us-breadcrumb' => CMSResource::collection($aboutUsBreadcrumb),
                'gallery' => $gallery,
                'about-contact-breadcrumb' => CMSResource::collection($aboutContactBreadcrumb),
            ]
        ], 'About page data retrieved successfully');
    }
}
