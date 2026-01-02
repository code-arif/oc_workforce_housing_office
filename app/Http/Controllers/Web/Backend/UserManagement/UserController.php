<?php

namespace App\Http\Controllers\Web\Backend\UserManagement;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:user-management.users.list')->only('index', 'getData');
        $this->middleware('permission:user-management.users.create')->only('create', 'store');
        $this->middleware('permission:user-management.users.edit')->only('edit', 'update');
        $this->middleware('permission:user-management.users.delete')->only('destroy');
    }
    
    /**
     * Display a listing of users
     */
    public function index()
    {
        $roles = Role::all();
        
        return view('backend.user-management.users.index', compact('roles'));
    }

    public function getData(Request $request)
    {
        if($request->ajax()) {
            $users = User::with('roles')->get();

             return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('name', function($item){
                    return $item->name;
                })
                ->addColumn('email', function($item){
                    return $item->email;
                })
                ->addColumn('phone', function($item){
                    return $item->phone;
                })
                ->addColumn('roles', function($item){
                    return $item->roles()->count() > 0 ? '<span class="badge bg-info">' . $item->roles()->pluck('display_name')->implode(', ') . ' </span>' : 'No Role Assigned';
                })
                ->addColumn('action', function($item){
                    return '
                        <button class="btn btn-sm btn-warning me-1" onclick="editUser(' . $item->id . ')" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser(' . $item->id . ')" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action', 'roles'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('backend.user-management.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ]);

    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load('roles');
        return view('backend.user-management.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $user->load('roles');
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
        // return view('backend.user-management.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);

        // return redirect()->route('user-management.users.index')
        //     ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
        // return redirect()->route('user-management.users.index')
        //     ->with('success', 'User deleted successfully');
    }
}
