<?php

namespace App\Http\Controllers\Web\Backend\CMS\Pricing;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class PricingPageController extends Controller
{
    /**
     * Update pricing page hero section
     */
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'pricing')
                ->where('section', 'hero')
                ->where('name', 'item')
                ->first();

            // handle image if present in request
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/pricing/hero');
                $validated_data['image'] = $image_path;
            }

            // Add additional data
            $validated_data['page'] = 'pricing';
            $validated_data['section'] = 'hero';
            $validated_data['name'] = 'item';

            CMS::updateOrCreate(
                [
                    'page' => 'pricing',
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


    public function index(Request $request)
    {
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

        return view('backend.pricing.plans.index');
    }

    /**
     * Update pricing plans
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'              => 'required|string|max:255|unique:pricing_plans,name',
                'price_per_bed'     => 'required|numeric|min:0',
                'tenants_per_room'  => 'required|integer|min:1|max:20',
                'amenities'         => 'nullable|array',
                'amenities.*'       => 'string|max:100',
                'description'       => 'nullable|string',
            ]);

            PricingPlan::create([
                'name'              => $request->name,
                'price_per_bed'     => $request->price_per_bed,
                'tenants_per_room'  => $request->tenants_per_room,
                'amenities'         => $request->amenities ?? [],
                'description'       => $request->description,
                'is_active'         => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pricing plan created successfully!'
            ], 201);
        } catch (Exception $e) {
            Log::error('Pricing Plan Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update pricing item
     */
    public function updatePricingItem(Request $request)
    {
        try {
            $request->validate([
                'id'                => 'required|exists:pricing_plans,id',
                'name'              => 'required|string|max:255|unique:pricing_plans,name,' . $request->id,
                'price_per_bed'     => 'required|numeric|min:0',
                'tenants_per_room'  => 'required|integer|min:1|max:20',
                'amenities'         => 'nullable|array',
                'amenities.*'       => 'string|max:100',
                'description'       => 'nullable|string',
            ]);

            $plan = PricingPlan::findOrFail($request->id);
            $plan->update([
                'name'              => $request->name,
                'price_per_bed'     => $request->price_per_bed,
                'tenants_per_room'  => $request->tenants_per_room,
                'amenities'         => $request->amenities ?? [],
                'description'       => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pricing plan updated successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete pricing item
     */
    public function destroy(Request $request)
    {
        try {
            $plan = PricingPlan::findOrFail($request->id);
            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pricing plan deleted successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete plan'
            ], 500);
        }
    }

    // Toggle status
    public function toggleStatus(Request $request)
    {
        $plan = PricingPlan::findOrFail($request->id);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return response()->json(['success' => true]);
    }
}
