<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EmployeeAssignController extends Controller
{
    /**
     * Assign employee(s) to a team
     * This now handles UPDATE instead of just INSERT
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $teamId = $request->team_id;
            $userIds = $request->user_ids;

            // Check if any selected user is already in a DIFFERENT team
            foreach ($userIds as $userId) {
                $existingTeam = TeamUser::where('user_id', $userId)
                    ->where('team_id', '!=', $teamId)
                    ->first();

                if ($existingTeam) {
                    $user = User::find($userId);
                    $team = Team::find($existingTeam->team_id);

                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "User '{$user->name}' is already assigned to team '{$team->name}'.",
                    ], 409);
                }
            }

            // Remove all existing assignments for this team
            TeamUser::where('team_id', $teamId)->delete();

            // Insert new assignments
            foreach ($userIds as $userId) {
                TeamUser::create([
                    'team_id' => $teamId,
                    'user_id' => $userId,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Team assignment updated successfully.',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit assigning employee
     * Returns only unassigned employees + current team's employees
     */
    public function edit($teamId)
    {
        try {
            // Check if team exists
            $team = Team::find($teamId);
            if (!$team) {
                return response()->json([
                    'status' => false,
                    'message' => 'Team not found'
                ], 404);
            }

            // Get users already assigned to THIS team
            $assignedToThisTeam = TeamUser::where('team_id', $teamId)
                ->with('user:id,name')
                ->get()
                ->pluck('user')
                ->filter(); // Remove null values if any

            // Get IDs of users assigned to ANY team
            $assignedUserIds = TeamUser::pluck('user_id')->toArray();

            // Get all employees who are NOT assigned to any team
            // OR are assigned to THIS team (so they can be removed/kept)
            $allUsers = User::where('role', 'employee')
                ->where(function ($query) use ($assignedUserIds, $teamId, $assignedToThisTeam) {
                    $query->whereNotIn('id', $assignedUserIds)
                        ->orWhereIn('id', $assignedToThisTeam->pluck('id')->toArray());
                })
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);

            return response()->json([
                'status' => true,
                'all_users' => $allUsers,
                'assigned_users' => $assignedToThisTeam
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}
