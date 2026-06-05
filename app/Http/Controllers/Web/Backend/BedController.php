<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Amenities;
use Yajra\DataTables\Facades\DataTables;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $beds = Bed::with('room', 'amenities')->latest('id')->get();
            // dd($beds);
            return DataTables::of($beds)
                ->addIndexColumn()
                ->addColumn('bed_label', function ($item) {
                    return '
                        <span class="fw-bold">Bed No. :' . $item->bed_number . '</span> <br>
                        <span class="text-capitalize">' . $item->bed_label . '</span>
                    ';
                })
                ->addColumn('description', function ($item) {
                    $amenities = $item->amenities->pluck('name')->join(', ');
                    return '
                        Property : <span class="fw-bold">' . $item->room->unit->property->name . '</span>
                        Unit : <span class="fw-bold">' . $item->room->unit->name . '</span>
                        Room: <span class="text-capitalize"> ' . $item->room->room_number . '</span> <br>
                        Amenities: <span class="text-muted">' . ($amenities ?: 'None') . '</span>
                    ';
                })
                // ->addColumn('room', fn($item) => $item->room->room_number ?? '---')
                ->addColumn('status', function ($item) {
                    $status = $item->is_occupied
                        ? '<button type="button" onclick="toggleBedStatus(' . $item->id . ')" class="badge bg-danger">Occupied</button>'
                        : '<button type="button" onclick="toggleBedStatus(' . $item->id . ')" class="badge bg-success">Available</button>';

                    return $status;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button"
                                    class="btn btn-sm btn-warning"
                                    onclick="editBed(' . $item->id . ')"
                                    title="Edit">
                                <i class="fe fe-edit"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="deleteBed(' . $item->id . ')"
                                    title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['bed_label', 'description', 'beds_count', 'status', 'actions'])
                ->make(true);
        }

        // return view('backend.layouts.properties.bed.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            // 'bed_label' => 'required|string|max:255',
            'bed_number' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_occupied' => 'boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);
        // dd($request->all());

        $validated['is_occupied'] = $request->has('is_occupied') ? true : false;
        if (Bed::where('room_id', $request->room_id)->where('bed_number', $request->bed_number)->exists()) {
            return response()->json([
                'error' => false,
                'message' => 'Bed already exists.',
            ]);
        }

        try {
            $room = Room::findOrFail($validated['room_id']);
            $validated['room_number'] = $room->room_number;
            $validated['bed_label'] = $room->unit->name . '-' . $room->room_number . '-' . $validated['bed_number'];
            $bed = Bed::create($validated);
            $bed->amenities()->sync($request->input('amenities', []));

            return response()->json([
                'success' => true,
                'message' => 'Bed created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => false,
                'message' => 'Error creating bed type: ' . $e->getMessage(),
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
    public function edit($id)
    {
        try {
            $bed = Bed::with('room', 'room.unit', 'room.unit.property', 'amenities')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $bed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found.' . $e->getMessage(),
            ], 404);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            // 'bed_label' => 'required|string|max:255',
            'bed_number' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_occupied' => 'boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);
        // dd($request->all());

        $validated['is_occupied'] = $request->has('is_occupied') ? true : false;

        if (
            Bed::where('room_id', $request->room_id)
            ->where('bed_number', $request->bed_number)
            ->where('id', '!=', $id)
            ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Bed already exists in this room.',
            ], 422);
        }

        try {
            $bed = Bed::findOrFail($id);
            $room = Room::findOrFail($validated['room_id']);
            $validated['room_number'] = $room->room_number;
            $validated['bed_label'] = $room->unit->name . '-' . $room->room_number . '-' . $validated['bed_number'];
            $bed->update($validated);
            $bed->amenities()->sync($request->input('amenities', []));

            return response()->json([
                'success' => true,
                'message' => 'Bed Updated successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating bed type: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $bed = Bed::findOrFail($id);
            $bed->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bed deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Bed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $bed = Bed::findOrFail($id);
            $bed->is_occupied = !$bed->is_occupied;
            $bed->save();

            return response()->json([
                'success' => true,
                'message' => 'Bed status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Bed status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete beds.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:beds,id',
        ]);

        try {
            $ids = $request->input('ids');
            $deletedCount = Bed::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted $deletedCount bed(s).",
                'count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting beds: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getRooms($unitId)
    {
        try {
            $rooms = Room::where('unit_id', $unitId)->where('is_active', true)->get();
            return response()->json([
                'success' => true,
                'data' => $rooms,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching rooms: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAmenities()
    {
        try {
            $amenities = Amenities::where('is_active', true)->get();
            return response()->json([
                'success' => true,
                'data' => $amenities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching amenities: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getBeds($roomId)
    {
        try {
            $beds = Bed::where('room_id', $roomId)->get();
            return response()->json([
                'success' => true,
                'data' => $beds,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching beds: ' . $e->getMessage(),
            ], 500);
        }
    }
}
