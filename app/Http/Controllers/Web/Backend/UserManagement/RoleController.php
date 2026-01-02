<?php

namespace App\Http\Controllers\Web\Backend\UserManagement;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:user-management.roles.list')->only('index', 'getData');
        $this->middleware('permission:user-management.roles.create')->only('create', 'store');
        $this->middleware('permission:user-management.roles.edit')->only('edit', 'update');
        $this->middleware('permission:user-management.roles.delete')->only('destroy');
    }
    /**
     * Display a listing of roles
     */
    public function index()
    {
        
        return view('backend.user-management.roles.index');
    }

    public function getData(Request $request)
    {
        if($request->ajax()) {
            $roles = Role::with('permissions')->get();

             return DataTables::of($roles)
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
                ->addColumn('permissions', function($item){
                    return '<small>' . $item->permissions->count() . ' permission(s)</small>';
                })
                ->addColumn('action', function($item){
                    return '
                        <a href="' . route('user-management.roles.edit', $item->id) . '" class="btn btn-sm btn-warning me-1" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="deleteRole(' . $item->id . ')" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action', 'permissions'])
                ->make(true);
        }
    }
    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($item) {
            return explode('.', $item->name)[0];
        });
        
        return view('backend.user-management.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => strtolower($validated['name']),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('user-management.roles.index')
            ->with('success', 'Role created successfully');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        return view('backend.user-management.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy(function ($item) {
            return explode('.', $item->name)[0];
        });
        
        return view('backend.user-management.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role in storage
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => strtolower($validated['name']),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('user-management.roles.index')
            ->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified role from storage
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of admin role
        if ($role->name === 'admin') {
            return redirect()->route('user-management.roles.index')
                ->with('error', 'Cannot delete the Admin role');
        }

        $role->delete();
        
        return redirect()->route('user-management.roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
