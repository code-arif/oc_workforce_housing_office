<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    use ApiResponse;

    /*
    ** User login
    */
    public function login(Request $request)
    {
        try {
            // validate input
            $validator = Validator::make($request->all(), [
                'login' => 'required|string', // email or phone
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation failed', 422);
            }

            $login = $request->input('login');
            $password = $request->input('password');

            // Find user by email OR phone
            $user = User::where('email', $login)
                ->orWhere('phone', $login)
                ->first();

            if (! $user) {
                return $this->error([], 'Invalid credentials', 401);
            }

            if ($user->password !== $password) {
                return $this->error([], 'Invalid credentials', 401);
            }

            // Get user's team information
            $teamInfo = DB::table('team_users')
                ->join('teams', 'team_users.team_id', '=', 'teams.id')
                ->where('team_users.user_id', $user->id)
                ->select(
                    'team_users.team_id',
                    'teams.name as team_name',
                    'team_users.is_leader',
                    'team_users.is_tracking_active'
                )
                ->first();

            // Generate JWT token for this user
            $token = JWTAuth::fromUser($user);

            $data = [
                'user'  => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'team' => $teamInfo ? [
                    'team_id' => $teamInfo->team_id,
                    'team_name' => $teamInfo->team_name,
                    'is_leader' => (bool) $teamInfo->is_leader,
                    'is_tracking_active' => (bool) $teamInfo->is_tracking_active,
                ] : null, // null if user is not in any team
                'token' => $token,
            ];

            return $this->success($data, 'Successfully logged in!', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /*
    ** User logout
    */
    public function logout()
    {
        try {

            auth('api')->logout();
            return $this->success([], 'Successfully logged out.', 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
