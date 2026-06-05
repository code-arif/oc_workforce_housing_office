<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Room;
use App\Models\Unit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PropertyController extends Controller
{
    public function __construct()
    {
        // You can add middleware here for permissions if needed
        $this->middleware('permission:property.list')->only('index', 'getData');
        $this->middleware('permission:property.create')->only('create', 'store');
        $this->middleware('permission:property.edit')->only('edit', 'update');
        $this->middleware('permission:property.delete')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        return view('backend.layouts.properties.layout.property-layout');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $properties = Property::with('leases')->latest('id')->get();

            return DataTables::of($properties)
                ->addIndexColumn()
                ->addColumn('name', function ($item) {
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
                    // Get active leases for this property
                    $activeLeases = $item->leases()->where('status', 'active')->get();

                    // Calculate totals
                    $totalRent = 0;
                    $totalPaid = 0;
                    $totalDue = 0;

                    foreach ($activeLeases as $lease) {
                        // Get all invoices for this lease (including soft deleted if needed)
                        $invoices = $lease->invoices()
                            ->whereNull('deleted_at') // Only non-deleted invoices
                            ->get();

                        foreach ($invoices as $invoice) {
                            $totalRent += $invoice->total_amount;
                            $totalPaid += $invoice->paid_amount ?? 0;
                            $totalDue += ($invoice->total_amount - ($invoice->paid_amount ?? 0));
                        }
                    }

                    $rent = '
                        Total Rent: <span class="fw-bold">$' . number_format($totalRent, 2) . '</span> <br>
                        Paid: <span class="fw-bold">$' . number_format($totalPaid, 2) . '</span> <br>
                        Due: <span class="fw-bold">$' . number_format($totalDue, 2) . '</span>
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
                    $buttons = '<div class="btn-group" role="group">';

                    if (auth()->user()->can('property.show')) {
                        $buttons .= '
                            <a href="' . route('property.show', $item->id) . '"
                            class="btn btn-sm btn-info"
                            title="View">
                                <i class="fe fe-eye"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('property.edit')) {
                        $buttons .= '
                            <button type="button"
                                    class="btn btn-sm btn-warning"
                                    onclick="editProperty(' . $item->id . ')"
                                    title="Edit">
                                <i class="fe fe-edit"></i>
                            </button>
                        ';
                    }

                    if (auth()->user()->can('property.delete')) {
                        $buttons .= '
                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="propertyDeleteConfirm(' . $item->id . ')"
                                    title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        ';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['name', 'rent', 'description', 'status', 'actions'])
                ->make(true);
        }
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
        } catch (Exception $e) {
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
            'units',
            'units.rooms',
            'units.rooms.beds',
            'units.rooms.beds.leaseAssignments.lease.tenant',
            'leases.tenant',
            'leases.season',
            'leases.assignments.bed.room.unit',
        ])->findOrFail($id);

        // Calculate summary stats
        $totalBeds = $property->totalBeds();
        $occupiedBeds = Bed::whereIn(
            'room_id',
            Room::whereIn('unit_id', $property->units->pluck('id'))->pluck('id')
        )
            ->where('is_occupied', true)
            ->count();

        $availableBeds = $totalBeds - $occupiedBeds;

        // Calculate rental stats
        $activeLeases = $property->leases->where('status', '!=', 'TERMINATED')->where('status', '!=', 'COMPLETED');
        $totalMonthlyRent = $activeLeases->sum('rent_amount');

        // Calculate totals
        $totalRent = 0;
        $totalPaid = 0;
        $totalDue = 0;

        foreach ($activeLeases as $lease) {
            // Get all invoices for this lease (including soft deleted if needed)
            $invoices = $lease->invoices()
                ->whereNull('deleted_at') // Only non-deleted invoices
                ->get();

            foreach ($invoices as $invoice) {
                $totalRent += $invoice->total_amount;
                $totalPaid += $invoice->paid_amount ?? 0;
                $totalDue += ($invoice->total_amount - ($invoice->paid_amount ?? 0));
            }
        }

        $stats = [
            'total_beds' => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'available_beds' => $availableBeds,
            'occupancy_rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0,
            'active_leases' => $activeLeases->count(),
            'total_monthly_rent' => $totalMonthlyRent,
            'total_rent' => $totalRent,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
        ];

        return view('backend.layouts.properties.show', compact('property', 'stats'));
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
        } catch (Exception $e) {
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
        $property = Property::findOrFail($id);
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

            // if ($request->hasFile('image_path')) {
            //     if ($property->image_path && Storage::disk('public')->exists($property->image_path)) {
            //         Storage::disk('public')->delete($property->image_path);
            //     }

            //     $validated['image_path'] = $request->file('image_path')->store('properties', 'public');
            // }

            $validated['slug'] = Str::slug($validated['name']);
            // Create property
            $property->update($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully.',
                'property' => $property,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);

        try {
            if ($property->units()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete property with associated units.',
                ], 400);
            }
            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get soft-deleted (trashed) properties for DataTable
     */
    public function getTrashData(Request $request)
    {
        if ($request->ajax()) {
            // Get only soft-deleted properties
            $properties = Property::onlyTrashed()->latest('deleted_at')->get();

            return DataTables::of($properties)
                ->addIndexColumn()
                ->addColumn('name', function ($item) {
                    return '<span class="fw-bold">' . htmlspecialchars($item->name) . '</span>';
                })
                ->addColumn('address', function ($item) {
                    return htmlspecialchars($item->address ?? 'N/A');
                })
                ->addColumn('type', function ($item) {
                    return $item->propertyType?->name ?? 'N/A';
                })
                ->addColumn('deleted_at', function ($item) {
                    return $item->deleted_at->format('M d, Y h:i A');
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" onclick="restoreProperty(' . $item->id . ')" title="Restore Property">
                                <i class="fa-solid fa-rotate-left"></i> Restore
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="permanentlyDeleteProperty(' . $item->id . ')" title="Permanently Delete">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['actions', 'name'])
                ->make(true);
        }
    }

    /**
     * Restore a soft-deleted property
     */
    public function restore($id)
    {
        try {
            $property = Property::onlyTrashed()->where('id', $id)->firstOrFail();

            $property->restore();

            return response()->json([
                'success' => true,
                'message' => 'Property "' . $property->name . '" restored successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring property: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete a soft-deleted property
     */
    public function forceDelete($id)
    {
        try {
            $property = Property::onlyTrashed()->where('id', $id)->firstOrFail();

            // Get property name before deletion
            $propertyName = $property->name;

            // Force delete the property (permanently removes from database)
            $property->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Property "' . $propertyName . '" permanently deleted!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting property: ' . $e->getMessage(),
            ], 500);
        }
    }
}
