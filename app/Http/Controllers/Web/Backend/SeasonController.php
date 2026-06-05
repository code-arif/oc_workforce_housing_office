<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class SeasonController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seasons.list')->only('index', 'getData');
        $this->middleware('permission:seasons.create')->only('create', 'store');
        $this->middleware('permission:seasons.edit')->only('edit', 'update');
        $this->middleware('permission:seasons.delete')->only('destroy');
        $this->middleware('permission:seasons.toggle.status')->only('toggleStatus');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('backend.layouts.leases.season.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $seasons = Season::latest('id')->get();

            return DataTables::of($seasons)
                ->addIndexColumn()
                ->addColumn('name', fn($item) => $item->name)
                ->addColumn('date', function ($item) {
                    return date('d M, Y', strtotime($item->blanket_start_date)) . ' - ' . date('d M, Y', strtotime($item->blanket_end_date));
                })
                ->addColumn('status', function ($item) {
                    $badge = $item->is_active
                        ? '<button onclick="toggleSeasonStatus(' . $item->id . ')" class="badge bg-success">Available</button>'
                        : '<button onclick="toggleSeasonStatus(' . $item->id . ')" class="badge bg-danger">Unavailable</button>';
                    return $badge;
                })
                ->addColumn('actions', function ($item) {
                    $buttons = '<div class="btn-group" role="group">';

                    if (auth()->user()->can('seasons.edit')) {
                        $buttons .= '
                            <button type="button"
                                    class="btn btn-sm btn-warning"
                                    onclick="editSeason(' . $item->id . ')"
                                    title="Edit">
                                <i class="fe fe-edit"></i>
                            </button>
                        ';
                                    }

                                    if (auth()->user()->can('seasons.delete')) {
                                        $buttons .= '
                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="showDeleteConfirm(' . $item->id . ')"
                                    title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        ';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }
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
            'blanket_start_date' => 'required|date',
            'blanket_end_date' => 'required|date',
        ]);

        DB::beginTransaction();

        $validated['blanket_start_date'] = date('Y-m-d', strtotime($validated['blanket_start_date']));
        $validated['blanket_end_date'] = date('Y-m-d', strtotime($validated['blanket_end_date']));
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Season::create($validated);

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Season created successfully.'], 200);
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
        $season = Season::findOrFail($id);
        return response()->json(['success' => true, 'data' => $season]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $season = Season::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blanket_start_date' => 'required|date',
            'blanket_end_date' => 'required|date',
        ]);

        DB::beginTransaction();

        $validated['blanket_start_date'] = date('Y-m-d', strtotime($validated['blanket_start_date']));
        $validated['blanket_end_date'] = date('Y-m-d', strtotime($validated['blanket_end_date']));
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $season->update($validated);

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Season updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $season = Season::findOrFail($id);
        $season->delete();

        return response()->json(['success' => true, 'message' => 'Season deleted successfully.'], 200);
    }

    public function toggleStatus($id)
    {
        try {
            $season = Season::findOrFail($id);
            $season->is_active = !$season->is_active;
            $season->save();

            return response()->json([
                'success' => true,
                'message' => 'Season status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Season status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
