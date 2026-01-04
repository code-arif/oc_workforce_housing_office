<?php

namespace App\Http\Controllers\Api\Tenents;

use Exception;
use App\Models\Tenant;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\TenantApplication\TenantWelcomeMail;
use App\Mail\TenantApplication\TenantApplicationReceivedAdminMail;

class LandingController extends Controller
{
    use ApiResponse;

    /**
     * Submit tenant mail
     */
    public function submitEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:tenants,email',
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

            $tenant = Tenant::create([
                'email' => $request->email,
                'application_source' => 'self',
                'status' => 'pending',
                'password' => Hash::make(Str::random(16)),
            ]);

            // Generate approval token
            $tenant->generateApprovalToken();

            // Send mail to admin
            Mail::to(config('mail.admin_email'))
                ->send(new TenantApplicationReceivedAdminMail($tenant));

            sleep(1);
            // Send welcome mail to tenant
            Mail::to($tenant->email)
                ->send(new TenantWelcomeMail($tenant));

            DB::commit();

            return $this->success([
                'tenant_id' => $tenant->id,
                'email' => $tenant->email,
                'status' => $tenant->status
            ], 'Your application has been submitted successfully. Please check your email.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Tenant application failed: ' . $e->getMessage());
            return $this->error([], 'Failed to submit application. Please try again.', 500);
        }
    }
}
