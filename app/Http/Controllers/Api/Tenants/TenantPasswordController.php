<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Http\Controllers\Controller;
use App\Mail\Tenant\PasswordReset\TenantPasswordResetOTPMail;
use App\Models\Tenant;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TenantPasswordController extends Controller
{
    use ApiResponse;

    /**
     * Set password after approval (First time setup)
     * Uses approval_token from approval email
     */
    public function setPasswordAfterApproval(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'approval_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        try {
            DB::beginTransaction();

            // 🔹 Find tenant by approval token
            $tenant = Tenant::where('approval_token', $request->approval_token)->first();

            if (! $tenant) {
                return $this->error([], 'Invalid or already used approval token.', 404);
            }

            // 🔹 Check token expiry
            if (
                $tenant->approval_token_expires_at &&
                now()->isAfter($tenant->approval_token_expires_at)
            ) {
                return $this->error([], 'Approval token has expired. Please contact support.', 403);
            }

            // 🔹 Check approval status
            if ($tenant->status == 'approved') {
                return $this->error([
                    'status' => $tenant->status,
                ], 'Your application is already approved.', 400);
            }

            // 🔹 Set password & activate account
            $tenant->password = Hash::make($request->password);

            // 🔹 Invalidate token (one-time use)
            $tenant->approval_token = null;
            $tenant->approval_token_expires_at = null;
            $tenant->status = 'approved'; // Change status to approved after password set

            $tenant->save();

            // 🔹 Auto login (JWT)
            $token = auth('api')->login($tenant);

            DB::commit();

            return $this->success([
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                    'profile' => $tenant->profile,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ], 'Password set successfully. You are now logged in.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Password setup failed', [
                'token' => $request->approval_token,
                'error' => $e->getMessage(),
            ]);

            return $this->error([], 'Failed to set password. Please try again.', 500);
        }
    }

    /**
     * Send OTP for forgot password (After first time setup)
     */
    public function sendForgotPasswordOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:tenants,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        try {
            DB::beginTransaction();

            $tenant = Tenant::where('email', $request->email)->first();

            // Check if password is set (can't reset if never set)
            if (! $tenant->password) {
                return $this->error([], 'Please set your password first using the approval link sent to your email.', 400);
            }

            // Check if account is active
            if (! in_array($tenant->status, ['approved'])) {
                return $this->error([
                    'status' => $tenant->status,
                ], 'Your account is not approved. Current status: ' . $tenant->status, 400);
            }

            // Generate OTP
            $tenant->generateOTP();

            // Send OTP email
            try {
                Mail::to($tenant->email)->send(new TenantPasswordResetOTPMail($tenant));
            } catch (Exception $mailError) {
                Log::error('Failed to send OTP email: ' . $mailError->getMessage());
                DB::rollBack();

                return $this->error([], 'Failed to send OTP. Please try again.', 500);
            }

            DB::commit();

            return $this->success([
                'email' => $tenant->email,
                'otp_expires_in_minutes' => 10,
            ], 'OTP sent to your email. Please check your inbox.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Send OTP failed: ' . $e->getMessage());

            return $this->error([], 'Failed to send OTP. Please try again.', 500);
        }
    }

    /**
     * Verify OTP and generate reset token
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:tenants,email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        try {
            $tenant = Tenant::where('email', $request->email)->first();

            // Verify OTP
            if (! $tenant->verifyOTP($request->otp)) {
                return $this->error([], 'Invalid or expired OTP.', 400);
            }

            // Generate password reset token
            $tenant->generatePasswordResetToken();

            return $this->success([
                'email' => $tenant->email,
                'reset_token' => $tenant->reset_password_token,
                'reset_url' => config('app.frontend_url') . "/reset-password?token={$tenant->reset_password_token}&email={$tenant->email}",
                'expires_in_minutes' => 60,
            ], 'OTP verified successfully. Use the reset token to change your password.', 200);
        } catch (Exception $e) {
            Log::error('OTP verification failed: ' . $e->getMessage());

            return $this->error([], 'OTP verification failed. Please try again.', 500);
        }
    }

    /**
     * Reset password using reset token (After OTP verification)
     */
    public function resetPasswordWithToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:tenants,email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        try {
            DB::beginTransaction();

            $tenant = Tenant::where('email', $request->email)->first();

            // Verify reset token
            if ($tenant->reset_password_token !== $request->reset_token) {
                return $this->error([], 'Invalid reset token.', 403);
            }

            // Check if token expired
            if ($tenant->reset_password_token_expire_at && now()->isAfter($tenant->reset_password_token_expire_at)) {
                return $this->error([], 'Reset token has expired. Please request a new OTP.', 403);
            }

            // Update password
            $tenant->password = Hash::make($request->password);

            // Clear all reset tokens
            $tenant->clearPasswordResetData();

            // Generate JWT token for auto-login
            $token = auth('api')->login($tenant);

            DB::commit();

            return $this->success([
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ], 'Password reset successfully. You are now logged in.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Password reset failed: ' . $e->getMessage());

            return $this->error([], 'Failed to reset password. Please try again.', 500);
        }
    }
}
