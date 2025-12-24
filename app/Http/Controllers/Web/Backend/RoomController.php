<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Room;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rooms = Room::with('beds')->latest('id')->get();
            // dd($rooms);
            return DataTables::of($rooms)
                ->addIndexColumn()
                ->addColumn('room_number', function($item) {
                    return '
                        Room No.: <span class="fw-bold">' . $item->room_number . '</span> <br>
                        <span class="text-muted">' . $item->name . '</span>
                    ';
                })
                ->addColumn('description', function ($item) {
                    return $item->description
                        ? (strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description)
                        : '---';
                })
                ->addColumn('gender', fn($item) => $item->gender_designation ?? '---')
                ->addColumn('beds_count', function ($item) {
                    $bedsData = $item->beds->map(function($bed) {
                        return [
                            'room' => $bed->room->room_number ?? '---',
                            'number' => $bed->bed_number ?? '---',
                            'is_active' => $bed->is_active
                        ];
                    })->toArray();
                    
                    $bedsJson = htmlspecialchars(json_encode($bedsData), ENT_QUOTES, 'UTF-8');
                    
                    return '<span class="badge bg-info beds-badge cursor-pointer" data-beds="' . $bedsJson . '" title="Hover for bed details">' 
                        . $item->beds->count() . ' Beds</span>';
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editRoom(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['room_number','beds_count', 'actions'])
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
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'gender_designation' => 'nullable|string|in:Male,Female,Mixed',
            'beds' => 'nullable|array',
            'beds.*.bed_number' => 'nullable|string|max:255',
        ]);

        try {
            // Create room
            $room = Room::create([
                'room_number' => $validated['room_number'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'gender_designation' => $validated['gender_designation'],
                'is_active' => true,
            ]);

            // Create beds if provided
            if (!empty($validated['beds'])) {
                foreach ($validated['beds'] as $bed) {
                    if (!empty($bed['bed_number'])) {
                        $room->beds()->create([
                            'bed_number' => $bed['bed_number'] ?? null,
                            'is_active' => false,
                        ]);
                    }
                }
            }

            return redirect()->route('rooms.list')
                ->with('success', 'Room created successfully with ' . count(array_filter($validated['beds'] ?? [], fn($b) => !empty($b['bed_label']) || !empty($b['bed_number']))) . ' beds.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating room: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit($id)
    {
        try {
            $room = Room::with('beds')->findOrFail($id);
            return view('backend.layouts.properties.room.edit', compact('room'));
        } catch (\Exception $e) {
            return redirect()->route('room.list')
                ->with('error', 'Room not found.');
        }
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $room = Room::with('beds')->findOrFail($id);

            $validated = $request->validate([
                'room_number' => 'required|string|max:255|unique:rooms,room_number,' . $id,
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'gender_designation' => 'nullable|string|in:Male,Female,Mixed',
                'beds' => 'nullable|array',
                'beds.*.id' => 'nullable|numeric',
                'beds.*.bed_number' => 'nullable|string|max:255',
            ]);

            // Update room
            $room->update([
                'room_number' => $validated['room_number'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'gender_designation' => $validated['gender_designation'],
            ]);

            // Handle beds update
            if (!empty($validated['beds'])) {
                $existingBedIds = $room->beds->pluck('id')->toArray();
                $updatedBedIds = [];

                foreach ($validated['beds'] as $bedData) {
                    // Skip empty beds
                    if ( empty($bedData['bed_number'])) {
                        continue;
                    }

                    if (!empty($bedData['id']) && in_array($bedData['id'], $existingBedIds)) {
                        // Update existing bed
                        Bed::where('id', $bedData['id'])->update([
                            'bed_number' => $bedData['bed_number'] ?? null,
                        ]);
                        $updatedBedIds[] = $bedData['id'];
                    } else {
                        // Create new bed
                        $bed = $room->beds()->create([
                            'bed_number' => $bedData['bed_number'] ?? null,
                            'is_active' => true,
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

            return redirect()->route('rooms.list')
                ->with('success', 'Room updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating room: ' . $e->getMessage());
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
            $room->beds()->delete();
            // Then delete room
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting room: ' . $e->getMessage(),
            ], 500);
        }
    }
}
