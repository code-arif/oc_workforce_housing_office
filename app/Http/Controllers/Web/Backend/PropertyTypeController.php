<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\PropertyType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class PropertyTypeController extends Controller
{
    /**
     * Display a listing of the property types.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $propertyTypes = PropertyType::latest('id')->get();

            return DataTables::of($propertyTypes)
                ->addIndexColumn()
                ->addColumn('name', fn($item) => $item->name)
                ->addColumn('slug', fn($item) => $item->slug)
                ->addColumn('description', function ($item) {
                    return $item->description
                        ? (strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description)
                        : '---';
                })
                ->addColumn('status', function ($item) {
                    $badge = $item->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                    return $badge;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editPropertyType(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePropertyType(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('backend.layouts.properties.types.index');
    }

    /**
     * Store a newly created property type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided

        $validated['slug'] = Str::slug($validated['name']);


        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            PropertyType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Property Type created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating property type: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified property type.
     */
    public function edit($id)
    {
        try {
            $propertyType = PropertyType::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $propertyType,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property Type not found.',
            ], 404);
        }
    }

    /**
     * Update the specified property type in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $propertyType = PropertyType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:property_types,name,' . $id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            // Generate slug if not provided

            $validated['slug'] = Str::slug($validated['name']);


            $validated['is_active'] = $request->has('is_active') ? true : false;

            $propertyType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Property Type updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating property type: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified property type from storage.
     */
    public function destroy($id)
    {
        try {
            $propertyType = PropertyType::findOrFail($id);
            $propertyType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property Type deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting property type: ' . $e->getMessage(),
            ], 500);
        }
    }
}
