<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Amenities;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $beds = Amenities::latest('id')->get();
            // dd($beds);
            return DataTables::of($beds)
                ->addIndexColumn()
                ->addColumn('name', function ($item) {
                    return '
                        <span class="fw-bold">' . $item->name . '</span>
                    ';
                })
                // ->addColumn('room', fn($item) => $item->room->room_number ?? '---')
                ->addColumn('status', function ($item) {
                    $status = $item->is_active
                        ? '<button type="button" onclick="toggleAmenityStatus(' . $item->id . ')" class="badge bg-success">Active</button>'
                        : '<button type="button" onclick="toggleAmenityStatus(' . $item->id . ')" class="badge bg-danger">Inactive</button>';

                    return $status;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button"
                                    class="btn btn-sm btn-warning"
                                    onclick="editAmenity(' . $item->id . ')"
                                    title="Edit">
                                <i class="fe fe-edit"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="deleteAmenity(' . $item->id . ')"
                                    title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['name', 'status', 'actions'])
                ->make(true);
        }

        // return view('backend.layouts.properties.bed.index');
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
            'name' => 'required|string|max:255',
        ]);
        // dd($request->all());

        $validated['is_active'] = $request->has('is_active') ? true : false;
        if (Amenities::where('name', $request->name)->exists()) {
            return response()->json([
                'error' => false,
                'message' => 'Amenity already exists.',
            ]);
        }

        try {

            Amenities::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Amenity created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => false,
                'message' => 'Error creating Amenity type: ' . $e->getMessage(),
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
        $amenity = Amenities::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $amenity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $amenity = Amenities::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        // dd($request->all());

        $validated['is_active'] = $request->has('is_active') ? true : false;
        if (Amenities::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'error' => false,
                'message' => 'Amenity already exists.',
            ]);
        }
        try {

            $amenity->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Amenity Update successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => false,
                'message' => 'Error creating Amenity: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $amenity = Amenities::findOrFail($id);
            $amenity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Amenity deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Amenity: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $amenity = Amenities::findOrFail($id);
            $amenity->is_active = !$amenity->is_active;
            $amenity->save();

            return response()->json([
                'success' => true,
                'message' => 'Amenity status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Amenity status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
