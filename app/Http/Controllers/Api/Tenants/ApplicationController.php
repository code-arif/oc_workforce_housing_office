<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Models\Application;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\TenantApplication\ReservationReceivedAdminMail;
use App\Mail\TenantApplication\ReservationSubmittedConfirmationMail;

class ApplicationController extends Controller
{
    use ApiResponse;

    /**
     * Submit single email application (from landing page)
     */
    public function submitSingleEmail(Request $request)
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

            // Check if email already submitted
            $existingApplication = Application::where('email', $request->email)
                ->whereNull('company_name')
                ->whereNull('reservation_item')
                ->first();

            if ($existingApplication) {
                return $this->error([], 'This email has already been submitted.', 400);
            }

            // Create single email application
            $application = Application::create([
                'status' => 'pending',
                'email' => $request->email,
            ]);

            // Send notification to admin
            try {
                // Mail::to(config('mail.admin_email'))
                //     ->queue(new ReservationReceivedAdminMail($application));
            } catch (Exception $mailError) {
                Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
            }

            DB::commit();

            return $this->success([
                'application_id' => $application->id,
                'email' => $application->email,
                'status' => $application->status,
            ], 'Thank you for your interest! We will contact you shortly.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Single email application submission failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to submit application. Please try again later.', 500);
        }
    }

    /**
     * Submit full reservation application (corporate/office)
     */
    public function submitReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Company information
            'company_name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'employee_count' => 'nullable|integer|min:1',

            // Contact person information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',

            // Reservation details
            'reservation_item' => 'required|array|min:1',
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

            // Check if company already has pending application
            $existingApplication = Application::where('company_name', $request->company_name)
                ->where('email', $request->email)
                ->where('status', 'pending')
                ->first();

            if ($existingApplication) {
                return $this->error([], 'A pending application already exists for this company.', 400);
            }

            // Create reservation application
            $application = Application::create([
                'status' => 'pending',

                // Contact person
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'job_title' => $request->job_title,

                // Company info
                'company_name' => $request->company_name,
                'industry' => $request->industry,
                'company_address' => $request->company_address,
                'employee_count' => $request->employee_count,

                // Reservation details
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
            sleep(3);

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
                'company_name' => $application->company_name,
                'email' => $application->email,
                'status' => $application->status,
            ], 'Your reservation request has been submitted successfully. We will contact you within 24 business hours.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Reservation application submission failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'company' => $request->company_name,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'Failed to submit application. Please try again later.', 500);
        }
    }
}
