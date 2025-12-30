<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Season;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $seasons = Season::latest('id')->get();

            return DataTables::of($seasons)
                ->addIndexColumn()
                ->addColumn('name', fn($item) => $item->name)
                ->addColumn('date', function ($item) {
                    return $item->blanket_start_date . ' - ' . $item->blanket_end_date;
                })
                ->addColumn('status', function ($item) {
                    $badge = $item->is_active
                        ? '<button onclick="toggleUnitStatus(' . $item->id . ')" class="badge bg-success">Available</button>'
                        : '<button onclick="toggleUnitStatus(' . $item->id . ')" class="badge bg-danger">Unavailable</button>';
                    return $badge;
                })
                ->addColumn('actions', function ($item) {
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editUnit(' . $item->id . ')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('backend.layouts.leases.season.index');
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
        //
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
        //
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
