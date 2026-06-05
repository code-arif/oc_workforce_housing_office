<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Unit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rooms = Room::with('beds', 'unit')->orderBy('unit_id')->orderBy('room_number')->get();
            // dd($rooms);
            return DataTables::of($rooms)
                ->addIndexColumn()
                ->addColumn('room_number', function ($item) {
                    return '
                        <a href="' . route('rooms.show', $item->id) . '" class="text-decoration-none fw-bold text-primary">
                            <span class="fw-bold">' . $item->room_number . '</span> <br>
                            <span class="text-muted">' . $item->name . '</span>
                        </a>
                    ';
                })
                ->addColumn('unit', fn($item) => $item->unit->name ?? '---')
                ->addColumn('beds_count', function ($item) {
                    $totalBeds = $item->beds->count();
                    $occupiedBeds = $item->beds->where('is_occupied', true)->count();
                    $availableBeds = $totalBeds - $occupiedBeds;

                    $bedsData = $item->beds->map(function ($bed) {
                        return [
                            'room' => $bed->room->room_number ?? '---',
                            'number' => $bed->bed_number ?? '---',
                            'is_occupied' => $bed->is_occupied
                        ];
                    })->toArray();

                    $bedsJson = htmlspecialchars(json_encode($bedsData), ENT_QUOTES, 'UTF-8');

                    return '<div class="d-flex gap-1 align-items-center" data-beds="' . $bedsJson . '" title="Hover for bed details">
                        <span class="badge bg-primary">' . $totalBeds . ' </span>
                        <span class="badge bg-success">' . $availableBeds . '</span>
                        <span class="badge bg-danger">' . $occupiedBeds . ' </span>
                    </div>';
                })
                ->addColumn('status', function ($item) {
                    $status = $item->is_active
                        ? '<button type="button" onclick="toggleRoomStatus(' . $item->id . ')" class="badge bg-success">Available</button>'
                        : '<button type="button" onclick="toggleRoomStatus(' . $item->id . ')" class="badge bg-danger">Unavailable</button>';

                    return $status;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button"
                                    class="btn btn-sm btn-warning"
                                    onclick="editRoom(' . $item->id . ')"
                                    title="Edit">
                                <i class="fe fe-edit"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="deleteRoom(' . $item->id . ')"
                                    title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['room_number', 'beds_count', 'status', 'actions'])
                ->make(true);
        }

        return view('backend.layouts.properties.room.index');
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        return view('backend.layouts.properties.room.create');
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'room_number' => 'required|string|max:255',
            'gender_designation' => 'nullable|string|in:male,female',
            'name' => 'nullable|string|max:255',
            'beds' => 'nullable|array',
            'beds.*.bed_number' => 'nullable|string|max:255',
        ]);
        // dd($request->all());
        $exsitingRoom = Room::where('unit_id', $validated['unit_id'])
            ->where('room_number', $validated['room_number'])
            ->exists();

        if ($exsitingRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Room number already exists in the same unit.'
            ]);
        }
        if (!empty($validated['beds'])) {
            $bedNumbers = collect($validated['beds'])
                ->pluck('bed_number')
                ->filter() // remove null / empty
                ->values();

            if ($bedNumbers->count() !== $bedNumbers->unique()->count()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate bed numbers are not allowed in the same room.'
                ]);
            }
        }
        try {
            DB::beginTransaction();
            // Create room
            $room = Room::create([
                'unit_id' => $validated['unit_id'],
                'room_number' => $validated['room_number'],
                'gender_designation' => $validated['gender_designation'] ?? 'male',
                'name' => $validated['name'],
                'is_active' => true,
            ]);
            // Create beds if provided
            if (!empty($validated['beds'])) {
                foreach ($validated['beds'] as $bed) {
                    if (!empty($bed['bed_number'])) {
                        $room->beds()->create([
                            'bed_number' => $bed['bed_number'] ?? null,
                            'bed_label' => $room->unit->name . '-' . $room->room_number . '-' . $bed['bed_number'],
                            'is_occupied' => false,
                        ]);
                    }
                }
            }
            DB::commit();
            // return redirect()->route('rooms.list')
            //     ->with('success', 'Room created successfully with ' . count(array_filter($validated['beds'] ?? [], fn($b) => !empty($b['bed_label']) || !empty($b['bed_number']))) . ' beds.');
            return response()->json(['success' => true, 'message' => 'Room created successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Room Creation Error' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error creating room: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified room.
     */
    public function show($id)
    {
        try {
            $room = Room::with('beds')->findOrFail($id);
            return view('backend.layouts.properties.room.show', compact('room'));
        } catch (Exception $e) {
            return redirect()->route('rooms.list')
                ->with('error', 'Room not found.');
        }
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit($id)
    {
        try {
            $room = Room::with('beds', 'unit')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $room]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $room = Room::with('beds')->findOrFail($id);

            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'room_number' => 'required|string|max:255',
                'name' => 'nullable|string|max:255',
                'gender_designation' => 'nullable|string|in:male,female,Mixed',
                'beds' => 'nullable|array',
                'beds.*.id' => 'nullable|numeric',
                'beds.*.bed_number' => 'nullable|string|max:255',
            ]);

            $exsitingRoom = Room::where('unit_id', $validated['unit_id'])
                ->where('room_number', $validated['room_number'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exsitingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room number already exists in the same unit.'
                ]);
            }
            // Update room
            $room->update([
                'unit_id' => $validated['unit_id'],
                'room_number' => $validated['room_number'],
                'name' => $validated['name'],
                'gender_designation' => $validated['gender_designation'] ?? 'male',
            ]);

            // Handle beds update
            if (!empty($validated['beds'])) {
                $existingBedIds = $room->beds->pluck('id')->toArray();
                $updatedBedIds = [];

                foreach ($validated['beds'] as $bedData) {

                    if (empty($bedData['bed_number'])) {
                        continue;
                    }

                    $duplicateExists = Bed::where('room_id', $room->id)
                        ->where('bed_number', $bedData['bed_number'])
                        ->when(!empty($bedData['id']), function ($q) use ($bedData) {
                            $q->where('id', '!=', $bedData['id']);
                        })
                        ->exists();

                    if ($duplicateExists) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bed number "' . $bedData['bed_number'] . '" already exists in this room.'
                        ]);
                    }

                    // Update or create
                    if (!empty($bedData['id']) && in_array($bedData['id'], $existingBedIds)) {
                        Bed::where('id', $bedData['id'])->update([
                            'bed_label' => $room->unit->name . '-' . $room->room_number . '-' . $bedData['bed_number'],
                            'bed_number' => $bedData['bed_number'],
                        ]);
                        $updatedBedIds[] = $bedData['id'];
                    } else {
                        $bed = $room->beds()->create([
                            'bed_number' => $bedData['bed_number'],
                            'bed_label' => $room->unit->name . '-' . $room->room_number . '-' . $bedData['bed_number'],
                            'is_occupied' => false,
                        ]);
                        $updatedBedIds[] = $bed->id;
                    }
                }

                // Delete beds that were removed
                Bed::where('room_id', $room->id)
                    ->whereNotIn('id', $updatedBedIds)
                    ->delete();
            } else {
                // Delete all beds if none provided
                $room->beds()->delete();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating room: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);
            // Delete beds first
            // $room->beds()->delete();
            if ($room->beds->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room with beds.',
                ], 500);
            }
            // Then delete room
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting room: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->is_active = !$room->is_active;
            $room->save();

            return response()->json([
                'success' => true,
                'message' => 'Room status updated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Room status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getUnits($propertyId)
    {
        try {
            $units = Unit::where('property_id', $propertyId)->where('is_active', true)->get();
            return response()->json([
                'success' => true,
                'data' => $units,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching units: ' . $e->getMessage(),
            ], 500);
        }
    }
}
