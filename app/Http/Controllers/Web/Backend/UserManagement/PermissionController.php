<?php

namespace App\Http\Controllers\Web\Backend\UserManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:user-management.permissions.list')->only('index', 'getData');
        $this->middleware('permission:user-management.permissions.create')->only('store');
        $this->middleware('permission:user-management.permissions.delete')->only('destroy');
    }
    /**
     * Display a listing of permissions
     */
    public function index()
    {        
        return view('backend.user-management.permissions.index');
    }

    public function getData(Request $request)
    {
        if($request->ajax()) {
            $permissions = Permission::latest('id')->get();

             return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('name', function($item){
                    return $item->name;
                })
                ->addColumn('display_name', function($item){
                    return $item->display_name;
                })
                ->addColumn('description', function($item){
                    return $item->description;
                })
                ->addColumn('action', function($item){
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editPermission(' . $item->id . ')" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePermission(' . $item->id . ')" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        return view('backend.user-management.permissions.create');
    }

    /**
     * Store a newly created permission in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        Permission::create([
            'name' => strtolower(str_replace(' ', '.', $validated['name'])),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully'
        ]);

        return redirect()->route('user-management.permissions.index')
            ->with('success', 'Permission created successfully');
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        return view('backend.user-management.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $permission
        ]);
    }

    /**
     * Update the specified permission in storage
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $permission->update([
            'name' => strtolower(str_replace(' ', '.', $validated['name'])),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully'
        ]);
    }

    /**
     * Remove the specified permission from storage
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully'
        ]);
    }
}
