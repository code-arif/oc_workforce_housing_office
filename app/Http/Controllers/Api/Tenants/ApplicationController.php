<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Models\Tenant;
use App\Models\Application;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\Application\ApplicationRejectionMail;
use App\Mail\TenantApplication\ReservationReceivedAdminMail;
use App\Mail\TenantApplication\ReservationSubmittedConfirmationMail;

class ApplicationController extends Controller
{
    use ApiResponse;

    /**
     * Submit individual tenant application
     */
    // public function submitIndividualApplication(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'first_name' => 'required|string|max:255',
    //         'middle_name' => 'nullable|string|max:255',
    //         'last_name' => 'required|string|max:255',
    //         'email' => 'required|email|max:100|unique:applications,email',
    //         'phone' => 'required|string|max:20',
    //         'reservation_item' => 'required|array',
    //         'reservation_item.*.property_name' => 'required|string',
    //         'reservation_item.*.is_interested' => 'required|boolean',
    //         'notes' => 'nullable|string',
    //     ], [
    //         'email.required' => 'Email address is required.',
    //         'email.email' => 'Please provide a valid email address.',
    //         'email.unique' => 'An application with this email already exists.',
    //         'first_name.required' => 'First name is required.',
    //         'last_name.required' => 'Last name is required.',
    //         'phone.required' => 'Phone number is required.',
    //         'reservation_item.required' => 'Please select at least one property.',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError(
    //             $validator->errors()->toArray(),
    //             'Validation failed',
    //             422
    //         );
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Create application with pending status
    //         $application = Application::create([
    //             'type' => 'individual',
    //             'status' => 'pending',
    //             'first_name' => $request->first_name,
    //             'middle_name' => $request->middle_name,
    //             'last_name' => $request->last_name,
    //             'email' => $request->email,
    //             'phone' => $request->phone,
    //             'reservation_item' => json_encode($request->reservation_item),
    //             'notes' => $request->notes,
    //         ]);

    //         // Send mail to admin for review
    //         try {
    //             Mail::to(config('mail.admin_email'))
    //                 ->queue(new ReservationReceivedAdminMail($application));
    //         } catch (Exception $mailError) {
    //             Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
    //         }

    //         // Short delay to avoid rate limiting
    //         sleep(1);

    //         // Send confirmation mail to applicant
    //         try {
    //             Mail::to($application->email)
    //                 ->queue(new ReservationSubmittedConfirmationMail($application));
    //         } catch (Exception $mailError) {
    //             Log::error('Failed to send confirmation email to applicant: ' . $mailError->getMessage());
    //         }

    //         DB::commit();

    //         return $this->success([
    //             'application_id' => $application->id,
    //             'type' => $application->type,
    //             'email' => $application->email,
    //             'status' => $application->status,
    //         ], 'Your reservation request has been submitted successfully. We will contact you within 24 hours.', 201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error('Individual application submission failed: ' . $e->getMessage(), [
    //             'email' => $request->email,
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return $this->error([], 'Failed to submit application. Please try again later.', 500);
    //     }
    // }

    /**
     * Submit corporate/office application
     */
    public function submitApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Company information
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',

            // Contact person information
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',

            // Reservation details
            'reservation_item' => 'nullable|array',
            'reservation_item.*.property_name' => 'nullable|string',
            'reservation_item.*.interested1' => 'nullable|string',
            'reservation_item.*.interested2' => 'nullable|string',
            'notes' => 'nullable|string',
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

            // Create corporate application with pending status
            $application = Application::create([
                'status' => 'pending',
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company_name' => $request->company_name,
                'industry' => $request->industry,
                'company_address' => $request->company_address,
                'employee_count' => $request->employee_count,
                'reservation_item' => json_encode($request->reservation_item),
                'notes' => $request->notes,
            ]);

            // Send mail to admin for review
            try {
                Mail::to(config('mail.admin_email'))
                    ->queue(new ReservationReceivedAdminMail($application));
            } catch (Exception $mailError) {
                Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
            }

            // Short delay to avoid rate limiting
            sleep(1);

            // Send confirmation mail to applicant
            try {
                Mail::to($application->email)
                    ->queue(new ReservationSubmittedConfirmationMail($application));
            } catch (Exception $mailError) {
                Log::error('Failed to send confirmation email to applicant: ' . $mailError->getMessage());
            }

            DB::commit();

            return $this->success([
                'application_id' => $application->id,
                'type' => $application->type,
                'company_name' => $application->company_name,
                'email' => $application->email,
                'status' => $application->status,
            ], 'Your reservation request has been submitted successfully. We will contact you within 24 business hours.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Application submission failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'company' => $request->company_name,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to submit application. Please try again later.', 500);
        }
    }

    /**
     * Handle admin's approval/rejection action
     * This will send tenant form link if approved
     */
    public function adminApplicationAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:applications,id',
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

            $application = Application::findOrFail($request->application_id);

            // Check if already processed
            if (in_array($application->status, ['approved', 'rejected'])) {
                return $this->error([
                    'current_status' => $application->status
                ], 'This application has already been processed.', 400);
            }

            // Update status
            $application->update([
                'status' => $request->status
            ]);

            $contactUrl = config('app.frontend_url') . "/contact";

            if ($request->status == 'rejected') {
                // Send rejection email
                // try {
                //     Mail::to($application->email)
                //         ->send(new ApplicationRejectionMail($application, $contactUrl));
                // } catch (Exception $mailError) {
                //     Log::error('Failed to send rejection email: ' . $mailError->getMessage());
                // }
            } else {
                // Status is approved - create tenant and send form link
                $tenant = $this->createTenantFromApplication($application);

                // Generate password reset token
                $passResetToken = $tenant->generateApprovalToken();
                $passResetUrl = config('app.frontend_url') . "/password-setup/{$tenant->approval_token}";

                // Send password setup email
                try {
                    Mail::to($tenant->email)
                        ->send(new \App\Mail\Tenant\TenantPasswordRestLinkMail($tenant, $passResetUrl));
                } catch (Exception $mailError) {
                    Log::error('Failed to send password setup email: ' . $mailError->getMessage());
                }
            }

            DB::commit();

            return $this->success([
                'application_id' => $application->id,
                'email' => $application->email,
                'status' => $application->status,
            ], "Application has been {$request->status} successfully.", 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin application action failed: ' . $e->getMessage());
            return $this->error([], 'Failed to process request.', 500);
        }
    }

    /**
     * Create tenant from approved application
     */
    private function createTenantFromApplication(Application $application)
    {
        $tenant = Tenant::create([
            'email' => $application->email,
            'application_source' => $application->type, // 'individual' or 'corporate'
            'status' => 'pending', // Will be changed to 'processing' after password setup
            'password' => Hash::make(Str::random(16)),
        ]);

        return $tenant;
    }
}
