<?php

namespace App\Http\Controllers\Web\Backend\UserManagement;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();
        
        return view('backend.user-management.users.index', compact('users', 'roles'));
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
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('user-management.users.index')
            ->with('success', 'User created successfully');
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
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        return view('backend.user-management.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
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

        return redirect()->route('user-management.users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('user-management.users.index')
            ->with('success', 'User deleted successfully');
    }
}
