<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TeamUserResource;

class UserListController extends Controller
{
    use ApiResponse;
    //user list with leader
    public function index()
    {
        try {
            $user = Auth::user();

            // User's first team with members
            $team = $user->teams()->with('users:id,name,email,phone,avatar')->first();

            if (!$team) {
                return $this->error([], 'User does not belong to any team.', 404);
            }

            // Format leader
            $leader = new TeamUserResource($user);

            // Format members (excluding leader)
            $members = TeamUserResource::collection(
                $team->users->where('id', '!=', $user->id)->values()
            );

            $data = [
                'team' => [
                    'id'   => $team->id,
                    'name' => $team->name,
                ],
                'leader'  => $leader,
                'members' => $members,
            ];

            return $this->success($data, 'Team user list retrieved successfully.');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
