<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\TenantDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Tenant\TenantResource;

class TenantProfileController extends Controller
{
    use ApiResponse;
    /**
     * Tenant Profile
     */
    public function profile(Request $request)
    {
        $tenant = auth('api')->user();

        $tenant->load([
            'profile',
            'address',
            'employments',
            'emergencyContacts',
            'documents'
        ]);

        return $this->success([
            'tenant' => new TenantResource($tenant)
        ], 'Tenant profile data fetched successfully!', 200);
    }

    /**
     * Update tenant profile
     */
    public function updateProfile(Request $request)
    {
        $tenant = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        try {
            if ($tenant->profile) {
                $tenant->profile->update($request->only([
                    'first_name',
                    'middle_name',
                    'last_name',
                    'phone',
                    'country_code'
                ]));
            }

            // Make avatar a full URL
            $profile = $tenant->profile->toArray();
            if ($profile['avatar']) {
                $profile['avatar'] = url($profile['avatar']);
            }

            return $this->success([
                'profile' => $tenant->profile
            ], 'Profile updated successfully', 200);
        } catch (Exception $e) {
            return $this->error('Failed to update profile', 500);
        }
    }

    /**
     * Update tenant avatar
     */
    public function updateAvatar(Request $request)
    {
        $validatedData = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $tenant = auth('api')->user();

        if (!empty($tenant->profile->avatar)) {
            Helper::deleteImage($tenant->profile->getRawOriginal('avatar'));
        }

        $validatedData['avatar'] = Helper::uploadImage($request->file('avatar'), 'tenant/avatar');

        $tenant->profile->update($validatedData);

        return $this->success([
            'id' => $tenant->id,
            'avatar' => $tenant->profile->avatar ? asset($tenant->profile->avatar) : asset('default/profile.png'),
            'created_at' => $tenant->created_at,
            'updated_at' => $tenant->updated_at,
        ], 'Avatar updated successfully', 200);
    }

    /**
     * Change User Password
     */
    public function changePassword(Request $request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'old_password'      => 'required',
            'new_password'      => 'required|min:6',
            'confirm_password'  => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error([], 'Old password does not match', 400);
        }

        // Update with new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success([], 'Password changed successfully', 200);
    }
}
