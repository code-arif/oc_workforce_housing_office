<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Models\Tenant;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\TenantDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TenantAuthController extends Controller
{
    use ApiResponse;

    /**
     * Tenant login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        $tenant = Tenant::where('email', $request->email)->first();

        if (!$tenant || !Hash::check($request->password, $tenant->password)) {
            return $this->error([], 'Invalid credentials', 401);
        }

        if ($tenant->status !== 'approved') {
            return $this->error(
                ['status' => $tenant->status],
                'Your account is not approved. Please contact administrator.',
                403
            );
        }

        // Generate JWT token
        $token = auth('api')->login($tenant);

        return $this->success(
            [
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                    'profile' => $tenant->profile,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ],
            'Login successful',
            200
        );
    }


    /**
     * User logout
     */
    public function logout(Request $request)
    {
        try {
            auth('api')->logout(); // JWT invalidate

            return $this->success([], 'Logged out successfully.', 200);
        } catch (Exception $e) {
            return $this->error([], 'Failed to logout.', 500);
        }
    }
}
