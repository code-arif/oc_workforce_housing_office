<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Unit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $units = Unit::latest('id')->get();

            return DataTables::of($units)
                ->addIndexColumn()
                ->addColumn('name', fn($item) => $item->name)
                ->addColumn('property', fn($item) => $item->property->name ?? '---')
                ->addColumn('status', function ($item) {
                    $badge = $item->is_active
                        ? '<button onclick="toggleUnitStatus(' . $item->id . ')" class="badge bg-success">Available</button>'
                        : '<button onclick="toggleUnitStatus(' . $item->id . ')" class="badge bg-danger">Unavailable</button>';
                    return $badge;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editUnit(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('backend.layouts.properties.unit.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'gender_designation' => 'nullable|string|in:male,female',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $exisingUnit = Unit::where('property_id', $validated['property_id'])->where('name', $validated['name'])->first();

        if ($exisingUnit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit already exists.',
            ]);
        }

        try {
            Unit::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating Unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         try {
            $unit = Unit::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $unit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $unit = Unit::findOrFail($id);

            $validated = $request->validate([
                'property_id' => 'required|exists:properties,id',
                'name' => 'required|string|max:255',
                'gender_designation' => 'nullable|string|in:male,female',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $exisingUnit = Unit::where('property_id', $validated['property_id'])->where('name', $validated['name'])->first();

            if ($exisingUnit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit already exists.',
                ]);
            }
            $unit->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $unit = Unit::findOrFail($id);

            if($unit->rooms()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit has rooms. You cannot delete it.',
                ], 500);
            }
            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unit deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Unit: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $unit = Unit::findOrFail($id);
            $unit->is_active = !$unit->is_active;
            $unit->save();

            return response()->json([
                'success' => true,
                'message' => 'Unit status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Unit status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
