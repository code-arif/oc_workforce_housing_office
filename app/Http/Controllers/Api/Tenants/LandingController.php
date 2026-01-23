<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Models\Tenant;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\Tenant\Application\ApplicationRejectionMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Mail\Tenant\TenantPasswordRestLinkMail;
use App\Mail\TenantApplication\TenantWelcomeMail;
use App\Mail\TenantApplication\TenantFormLinkMail;
use App\Mail\TenantApplication\TenantEmailReceivedAdminMail;

class LandingController extends Controller
{
    use ApiResponse;

    /**
     * Submit tenant email and create initial application
     */
    public function submitEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:tenants,email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered as a tenant. Please use a different email or contact support.',
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

            // Create tenant with pending status
            $tenant = Tenant::create([
                'email' => $request->email,
                'application_source' => 'self',
                'status' => 'pending',
                'password' => Hash::make(Str::random(16)),
            ]);

            // Generate approval token (for admin to proceed)
            $approvalToken = $tenant->generateApprovalToken();

            // Send mail to admin with proceed and view buttons
            // try {
            //     Mail::to(config('mail.admin_email'))
            //         ->send(new TenantEmailReceivedAdminMail($tenant));
            // } catch (Exception $mailError) {
            //     Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
            // }

            // Short delay to avoid rate limiting
            // sleep(1);

            // Send welcome mail to tenant
            // try {
            //     Mail::to($tenant->email)
            //         ->send(new TenantWelcomeMail($tenant));
            // } catch (Exception $mailError) {
            //     Log::error('Failed to send welcome email to tenant: ' . $mailError->getMessage());
            // }

            DB::commit();

            return $this->success([
                'tenant_id' => $tenant->id,
                'email' => $tenant->email,
                'status' => $tenant->status,
                'approval_token' => $tenant->approval_token,
                'expires_at' => $tenant->approval_token_expires_at,
            ], 'Your application has been submitted successfully as a tenant.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Tenant email submission failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to submit application. Please try again later.', 500);
        }
    }

    /**
     * Handle admin's proceed action
     * This will change status to 'processing' and send form link to tenant
     */
    public function adminApplicationProceed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'approval_token' => 'required|string',
            'status' => 'required|in:approved,rejected',
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

            $tenant = Tenant::findOrFail($request->tenant_id);

            // Verify approval token
            if ($tenant->approval_token !== $request->approval_token) {
                return $this->error([], 'Invalid approval token.', 403);
            }

            // Check if token expired
            if ($tenant->approval_token_expires_at && now()->isAfter($tenant->approval_token_expires_at)) {
                return $this->error([], 'Approval token has expired.', 403);
            }

            // Update status to processing
            $tenant->update([
                'status' => $request->status
            ]);

            // Generate new approval token for password reset (if approved)
            $passResetToken = null;
            if ($request->status == 'approved') {
                $passResetToken = $tenant->generateApprovalToken();
            }

            // Generate frontend form URL
            $passResetUrl = config('app.frontend_url') . "/reset-password/{$tenant->approval_token}";

            // Support URL
            $contactUrl = config('app.frontend_url') . "/contact";

            if ($request->status == 'rejected') {
                // Send rejection email logic can be added here
                Mail::to($tenant->email)->send(new ApplicationRejectionMail($tenant, $contactUrl));
            } else {
                // Send approval email logic can be added here
                Mail::to($tenant->email)->send(new TenantPasswordRestLinkMail($tenant, $passResetUrl));
            }

            DB::commit();

            return $this->success([
                'tenant_id' => $tenant->id,
                'email' => $tenant->email,
                'status' => $tenant->status,
                'password_reset_url' => $passResetUrl,
                'password_reset_token' => $tenant->approval_token,
            ], 'Password reset link generated and sent to tenant successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin proceed action failed: ' . $e->getMessage());
            return $this->error([], 'Failed to process request.', 500);
        }
    }
}
