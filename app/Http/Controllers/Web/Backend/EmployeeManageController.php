<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Team;
use App\Models\User;
use App\Models\Work;
use App\Helper\Helper;
use App\Models\TeamUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class EmployeeManageController extends Controller
{
    // list all employee
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('team')->where('role', '!=', 'admin')->latest('id');
            $users = $query->get();

            return DataTables::of($users)
                ->addIndexColumn()

                // Name
                ->addColumn('name', function ($item) {
                    return strlen($item->name) > 20 ? substr($item->name, 0, 20) . '...' : $item->name;
                })

                // Email
                ->addColumn('email', fn($item) => $item->email ?? '---')

                // Phone
                ->addColumn('phone', fn($item) => $item->phone ?? '---')

                // Password
                ->addColumn('password', fn($item) => $item->password ?? '---')

                // Address
                ->addColumn('address', function ($item) {
                    return $item->address
                        ? (strlen($item->address) > 25 ? substr($item->address, 0, 25) . '...' : $item->address)
                        : '---';
                })

                // Teams
                ->addColumn('team', function ($item) {
                    if (!$item->team || !$item->team->team) {
                        return '<span class="badge bg-secondary">No Team</span>';
                    }
                    return '<span class="badge bg-primary">'
                        . $item->team->team->name . ' </span>';
                })

                ->addColumn('avatar', function ($item) {
                    $avatarPath = $item->avatar
                        ? asset($item->avatar)
                        : asset('default/default_person.jpg');

                    return '<img src="' . $avatarPath . '" alt="avatar" class="avatar-img img-fluid">';
                })


                // Action buttons
                ->addColumn('action', function ($item) {
                    $actionButtons = '<div class="d-flex justify-content-start align-items-center gap-1">';

                    // Edit button
                    $actionButtons .= '<button type="button"
                                   class="btn btn-warning btn-sm editUser"
                                   data-id="' . $item->id . '">
                                   <i class="fa fa-pen-to-square"></i> Edit
                               </button>';

                    // Delete button
                    $actionButtons .= '<button type="button" class="btn btn-sm btn-danger deleteBtn"
                                   onclick="showDeleteConfirm(' . $item->id . ')">
                                   <i class="fa fa-trash"></i> Delete
                               </button>';

                    $actionButtons .= '</div>';

                    return $actionButtons;
                })

                ->rawColumns(['avatar', 'action', 'team'])
                ->make();
        }

        $teams = Team::get();

        return view("backend.layouts.users.index", compact('teams'));
    }

    // store employee
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'nullable|string|max:20|unique:users,phone',
                'password' => 'required|min:6',
                'address' => 'nullable|string|max:255',
                'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
                'team_id' => 'nullable|exists:teams,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = Helper::uploadImage($request->file('avatar'), 'avatars');
            }

            // Create employee user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => $request->password,
                'role'     => 'employee',
                'address'  => $request->address,
                'avatar'   => $avatarPath,
            ]);

            // Assign team if selected
            if ($request->filled('team_id')) {
                $teamId = $request->team_id;

                $alreadyAssigned = DB::table('team_users')
                    ->where('team_id', $teamId)
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$alreadyAssigned) {
                    DB::table('team_users')->insert([
                        'team_id'    => $teamId,
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Employee created successfully' . ($request->filled('team_id') ? ' and assigned to team.' : '.'),
                'data'    => $user,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    // edit employee
    public function edit($id)
    {
        try {
            $user = User::with('teams:id,name')->find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user: ' . $e->getMessage(),
            ], 500);
        }
    }



    // Update employee
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name'      => 'required|string|max:255',
                'email'     => 'nullable|email|unique:users,email,' . $user->id,
                'phone'     => 'nullable|string|max:20|unique:users,phone,' . $user->id,
                'password'  => 'nullable|string|min:6',
                'address'   => 'nullable|string|max:255',
                'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'team_id'   => 'nullable|exists:teams,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Handle avatar upload
            $avatarPath = $user->avatar;
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }
                $avatarPath = Helper::uploadImage($request->file('avatar'), 'avatars');
            }

            // Update user details
            $user->update([
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => $request->filled('password') ? $request->password : $user->password,
                'address'   => $request->address,
                'avatar'    => $avatarPath,
            ]);

            // Update or assign team
            if ($request->filled('team_id')) {
                $teamId = $request->team_id;

                // Check if already assigned to any team
                $existingTeam = DB::table('team_users')
                    ->where('user_id', $user->id)
                    ->first();

                if ($existingTeam) {
                    // If assigned to a different team, update it
                    if ($existingTeam->team_id != $teamId) {
                        DB::table('team_users')
                            ->where('user_id', $user->id)
                            ->update([
                                'team_id' => $teamId,
                                'updated_at' => now(),
                            ]);
                    }
                } else {
                    // If not assigned yet, assign now
                    DB::table('team_users')->insert([
                        'team_id'    => $teamId,
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'User updated successfully' . ($request->filled('team_id') ? ' and team assignment updated.' : '.'),
                'data'    => $user,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Delete employee
    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
        }

        try {
            // Delete image if exists
            if ($user->avatar) {
                Helper::deleteImage($user->image_path);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Employee.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
