<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $beds = Bed::with('room')->latest('id')->get();
            // dd($beds);
            return DataTables::of($beds)
                ->addIndexColumn()
                ->addColumn('bed_number', function($item) {
                    return $item->room->room_number . '-' . $item->bed_number;
                })
                ->addColumn('description', function ($item) {
                    return $item->description
                        ? (strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description)
                        : '---';
                })
                ->addColumn('room', fn($item) => $item->room->room_number ?? '---')
                ->addColumn('status', function ($item) {
                    $status = $item->is_active 
                    ? '<button type="button" onclick="toggleStatus(' . $item->id . ')" class="badge bg-danger">Occupied</button>'
                    : '<button type="button" onclick="toggleStatus(' . $item->id . ')" class="badge bg-success">Available</button>';
                    
                    return $status;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editbed(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['beds_count', 'status', 'actions'])
                ->make(true);
        }

        return view('backend.layouts.properties.bed.index');
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
            'is_active' => 'boolean',
        ]);
        // dd($request->all());

        $validated['is_active'] = $request->has('is_active') ? true : false;
        if(Bed::where('room_id', $request->room_id)->where('bed_label', $request->bed_label)->exists()) {
            return response()->json([
                'error' => false,
                'message' => 'Bed already exists.',
            ]);
        }

        try {
            
            Bed::create($validated);

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
            $bed = Bed::findOrFail($id);

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
            'is_active' => 'boolean',
        ]);
        // dd($request->all());

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $bed = Bed::findOrFail($id);
            $bed->update($validated);

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
            $bed->is_active = !$bed->is_active;
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
}
