<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Unit;
use App\Models\Property;
use Illuminate\Support\Str;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        if ($request->ajax()) {
            $properties = Property::latest('id')->get();

            return DataTables::of($properties)
                ->addIndexColumn()
                ->addColumn('name', function($item){
                    return '
                        <a href="' . route('property.show', $item->id) . '" class="text-decoration-none fw-bold text-primary">
                            <span class="fw-bold">' . $item->name . '</span> <br>
                        </a>
                    ';
                })
                ->addColumn('description', function ($item) {
                    return $item->address;
                })
                ->addColumn('rent', function ($item) {
                    $rent = '
                        Total Rent: <span class="fw-bold"> $20000.00</span> <br>
                        Deposit: <span class="fw-bold">$15500.00</span> <br>
                        Due: <span class="fw-bold">$4500.00</span>
                    ';
                    return $rent;
                })
                ->addColumn('status', function ($item) {
                    $badge = $item->is_active
                        ? '<button onclick="togglePropertyStatus(' . $item->id . ')" class="badge bg-success">Active</button>'
                        : '<button onclick="togglePropertyStatus(' . $item->id . ')" class="badge bg-danger">Inactive</button>';
                    return $badge;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <a href="' . route('property.show', $item->id) . '" class="btn btn-sm btn-info me-1" title="Show"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-warning me-1" onclick="editProperty(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="propertyDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['name', 'rent', 'description', 'status', 'actions'])
                ->make(true);
        }
        return view('backend.layouts.properties.layout.property-layout');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $propertyTypes = PropertyType::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        return view('backend.layouts.properties.create', compact('propertyTypes', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_type_id' => 'required|exists:property_types,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            DB::beginTransaction();
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('properties', 'public');
                $validated['image_path'] = $imagePath;
            }

            $validated['is_active'] = true;
            $validated['slug'] = Str::slug($validated['name']);
            // $validated['property_type_id'] = $validated['property_type_id'];
            // unset($validated['property_type_id']);

            // Create property
            $property = Property::create($validated);

            DB::commit();

            // return redirect()->route('property.list')->with('success', 'Property created successfully.');
            return response()->json([
                'success' => true,
                'message' => 'Property created successfully.',
                'property' => $property,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $property = Property::with([
            'propertyType',
            'units' => function ($query) {
                $query->withPivot('room_id', 'bed_id');
            }
        ])->findOrFail($id);

        // Load rooms and beds with their details
        $propertyUnits = [];
        foreach ($property->units as $unit) {
            $roomId = $unit->pivot->room_id;
            $bedId = $unit->pivot->bed_id;

            $room = $roomId ? \App\Models\Room::find($roomId) : null;
            $bed = $bedId ? \App\Models\Bed::find($bedId) : null;

            $propertyUnits[] = [
                'unit' => $unit,
                'room' => $room,
                'bed' => $bed,
            ];
        }

        // Count total beds
        $totalBeds = count($propertyUnits);

        return view('backend.layouts.properties.show', compact('property', 'propertyUnits', 'totalBeds'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $property = Property::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $property,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
