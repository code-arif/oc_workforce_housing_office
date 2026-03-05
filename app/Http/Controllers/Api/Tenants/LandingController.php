<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Models\Tenant;
use App\Models\Application;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\Tenant\TenantPasswordRestLinkMail;
use App\Mail\TenantApplication\TenantWelcomeMail;
use App\Mail\TenantApplication\TenantFormLinkMail;
use App\Mail\Tenant\Application\ApplicationRejectionMail;
use App\Mail\TenantApplication\TenantEmailReceivedAdminMail;

class LandingController extends Controller
{
    use ApiResponse;

    public function propertiesForForms()
    {
        try {
            $properties = DB::table('properties')
                ->select('id', 'name')
                ->where('is_active', true)
                ->get();

            return $this->success($properties, 'Properties retrieved successfully');
        } catch (Exception $e) {
            Log::error('Failed to retrieve properties for forms: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to retrieve properties. Please try again later.', 500);
        }
    }

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
            $tenant = Application::create([
                'email' => $request->email,
                'status' => 'pending',
            ]);

            // Generate approval token (for admin to proceed)
            $approvalToken = $tenant->generateApprovalToken();

            // Send mail to admin with proceed and view buttons
            try {
                Mail::to(config('mail.admin_email'))
                    ->send(new TenantEmailReceivedAdminMail($tenant));
            } catch (Exception $mailError) {
                Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
            }

            // Short delay to avoid rate limiting
            sleep(1);

            // Send welcome mail to tenant
            try {
                Mail::to($tenant->email)
                    ->send(new TenantWelcomeMail($tenant));
            } catch (Exception $mailError) {
                Log::error('Failed to send welcome email to tenant: ' . $mailError->getMessage());
            }

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
}
