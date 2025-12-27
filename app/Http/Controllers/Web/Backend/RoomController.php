<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rooms = Room::with('beds')->get();
            // dd($rooms);
            return DataTables::of($rooms)
                ->addIndexColumn()
                ->addColumn('room_number', function($item) {
                    return '
                        <a href="' . route('rooms.show', $item->id) . '" class="text-decoration-none fw-bold text-primary">
                            <span class="fw-bold">' . $item->room_number . '</span> <br>
                            <span class="text-muted">' . $item->name . '</span>
                        </a>
                    ';
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
                ->addColumn('status', function ($item) {
                    $status = $item->is_active 
                    ? '<button type="button" onclick="toggleStatus(' . $item->id . ')" class="badge bg-success">Available</button>'
                    : '<button type="button" onclick="toggleStatus(' . $item->id . ')" class="badge bg-danger">Unavailable</button>';
                    
                    return $status;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <a href="' . route('rooms.show', $item->id) . '" class="btn btn-sm btn-info me-1" title="Show"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-warning me-1" onclick="editRoom(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['room_number','beds_count', 'status', 'actions'])
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
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'gender_designation' => 'nullable|string|in:male,female',
            'name' => 'nullable|string|max:255',
            'beds' => 'nullable|array',
            'beds.*.bed_number' => 'nullable|string|max:255',
        ]);
        // dd($request->all());

        if (!empty($validated['beds'])) {
            Log::info('Bed Numbers: ' . json_encode($validated['beds']));
            $bedNumbers = collect($validated['beds'])
                ->pluck('bed_number')
                ->filter() // remove null / empty
                ->values();

            if ($bedNumbers->count() !== $bedNumbers->unique()->count()) {
                Log::error('Duplicate bed numbers are not allowed in the same room.');
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Duplicate bed numbers are not allowed in the same room.');
            }
        }
        Log::info('Validated Data: ' . json_encode($validated));
        try {
            DB::beginTransaction();
            // Create room
            $room = Room::create([
                'unit_id' => $validated['unit_id'],
                'room_number' => $validated['room_number'],
                'gender_designation' => $validated['gender_designation'],
                'name' => $validated['name'],
                'is_active' => true,
            ]);
            Log::info('Room Created: ' . json_encode($room));
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
            Log::info('Beds Created');
            DB::commit();
            return redirect()->route('rooms.list')
                ->with('success', 'Room created successfully with ' . count(array_filter($validated['beds'] ?? [], fn($b) => !empty($b['bed_label']) || !empty($b['bed_number']))) . ' beds.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Room Creation Error' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating room: ' . $e->getMessage());
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
        } catch (\Exception $e) {
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
            DB::beginTransaction();
            $room = Room::with('beds')->findOrFail($id);

            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'room_number' => 'required|string|max:255|unique:rooms,room_number,' . $id,
                'name' => 'nullable|string|max:255',
                'gender_designation' => 'nullable|string|in:male,female,Mixed',
                'beds' => 'nullable|array',
                'beds.*.id' => 'nullable|numeric',
                'beds.*.bed_number' => 'nullable|string|max:255',
            ]);

            // Update room
            $room->update([
                'unit_id' => $validated['unit_id'],
                'room_number' => $validated['room_number'],
                'name' => $validated['name'],
                'gender_designation' => $validated['gender_designation'],
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
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Bed number "' . $bedData['bed_number'] . '" already exists in this room.');
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
            return redirect()->route('rooms.list')
                ->with('success', 'Room updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Room status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
