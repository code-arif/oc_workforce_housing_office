<?php

namespace App\Http\Controllers\Api\Tenants;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenentApplicationRequest;
use App\Mail\TenantApplication\TenantFormSubmissionSuccessMail;
use App\Mail\TenantApplication\TenantFormSubmittedMail;
use App\Models\Application;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantFormController extends Controller
{
    use ApiResponse;

    // New application workflow - submit single email from landing page, then send form link to that email for full application submission

    public function submitApplication(TenentApplicationRequest $request, $approvalToken)
    {
        try {
            $validateToken = DB::table('application_tokens')
                ->where('email', $request->email)
                ->where('token', $approvalToken)
                ->where('expires_at', '>', now())
                ->first();

            if (!$validateToken) {
                return $this->error([], 'Invalid or expired application access token.', 403);
            }

            DB::beginTransaction();

            // Create application record
            $application = Application::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?? null,
                'last_name' => $request->last_name ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'gender' => $request->gender ?? null,
                'country_of_origin' => $request->country_of_origin ?? null,
                'date_of_birth' => $request->date_of_birth ?? null,
                'arrival_date' => $request->arrival_date ?? null,
                'departure_date' => $request->departure_date ?? null,
                'notes' => $request->notes ?? null,
                'application_type' => 'Rental Application',
                'application_number' => 'APP-' . strtoupper(Str::random(8)),
                'property_id' => $request->property_id ?? null,
                'sponsor_name' => $request->sponsor_name ?? null,
                'sponsor_city' => $request->sponsor_city ?? null,
                'sponsor_state' => $request->sponsor_state ?? null,
                'sponsor_zipcode' => $request->sponsor_zipcode ?? null,
                'sponsor_country' => $request->sponsor_country ?? null,
                'sponsor_contact_name' => $request->sponsor_contact_name ?? null,
                'sponsor_phone' => $request->sponsor_phone ?? null,
                'sponsor_email' => $request->sponsor_email ?? null,
                'sponsor_relationship' => $request->sponsor_relationship ?? null,
                'is_j1_sponsor' => $request->is_j1_sponsor ?? null,
            ]);

            // Create employment histories (multiple support)
            $employmentHistories = [];
            if ($request->has('employment_histories') && is_array($request->employment_histories)) {
                foreach ($request->employment_histories as $empData) {
                    $employmentHistories[] = [
                        'company_name' => $empData['company_name'] ?? null,
                        'company_address' => $empData['company_address'] ?? null,
                        'industry' => $empData['industry'] ?? null,
                        'job_title' => $empData['job_title'] ?? null,
                        'employer_contact_person_name' => $empData['employer_contact_person_name'] ?? null,
                        'employer_contact_person_phone' => $empData['employer_contact_person_phone'] ?? null,
                        'employer_contact_person_email' => $empData['employer_contact_person_email'] ?? null,
                    ];
                }
                $application->update([
                    'employer_info' => $employmentHistories
                ]);
            } else {
                // Single employment history (backward compatibility)
                $employerInfo = [
                    'company_name' => $request->employment_historie['company_name'] ?? null,
                    'company_address' => $request->employment_historie['company_address'] ?? null,
                    'industry' => $request->employment_historie['industry'] ?? null,
                    'job_title' => $request->employment_historie['job_title'] ?? null,
                    'employer_contact_person_name' => $request->employment_historie['employer_contact_person_name'] ?? null,
                    'employer_contact_person_phone' => $request->employment_historie['employer_contact_person_phone'] ?? null,
                    'employer_contact_person_email' => $request->employment_historie['employer_contact_person_email'] ?? null,
                ];

                $application->update([
                    'employer_info' => $employerInfo
                ]);
            }

            // Handle document uploads
            $documentFields = [
                'passport' => 'passport_copy',
                'visa' => 'visa_document',
                'id_front' => 'front_id_document',
                'id_back' => 'back_id_document',
            ];
            $uploadedDocuments = [];

            foreach ($documentFields as $inputName => $dbField) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $path = $file->store("application_documents/{$application->id}", 'public');

                    $application->update([
                        $dbField => $path,
                    ]);

                    $uploadedDocuments[] = $path;
                }
            }

            // Send email to admin about form submission for review
            try {
                Mail::to(config('mail.admin_email'))
                    ->send(new TenantFormSubmittedMail($application));
            } catch (Exception $mailError) {
                Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
            }

            // Short delay to avoid rate limiting
            sleep(1);

            // Send success mail to tenant
            try {
                Mail::to($application->email)
                    ->send(new TenantFormSubmissionSuccessMail($application));
            } catch (Exception $mailError) {
                Log::error('Failed to send success email to tenant: ' . $mailError->getMessage());
            }

            // Delete the used token
            DB::table('application_tokens')->where('email', $request->email)->where('token', $approvalToken)->delete();

            DB::commit();

            return $this->success([
                'applicant' => [
                    'id' => $application->id,
                    'email' => $application->email,
                    'status' => $application->status,
                    'application_number' => $application->application_number,
                ]
            ], 'Your application has been submitted successfully. We will review it and get back to you soon.', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error submitting tenant application: ' . $e->getMessage());
            return $this->error([], 'An error occurred while submitting the application.', 500);
        }
    }

    // public function submit(TenentApplicationRequest $request, $approvalToken)
    // {
    //     try {
    //         $tenantId = Tenant::select('id')
    //             ->whereNotNull('approval_token')
    //             ->where('approval_token',  $approvalToken)
    //             ->value('id');

    //         if (!$tenantId) {
    //             return $this->error([], 'Invalid or expired form access token.', 403);
    //         }

    //         DB::beginTransaction();

    //         $tenant = Tenant::find($tenantId);

    //         // Check if already submitted
    //         if ($tenant->profile) {
    //             DB::rollBack();
    //             return $this->error([
    //                 'status' => $tenant->status
    //             ], 'Application already submitted.', 400);
    //         }

    //         // Update tenant basic info
    //         $tenant->update([
    //             'date_of_birth' => $request->date_of_birth,
    //             'gender' => $request->gender,
    //             'move_in_date' => $request->move_in_date,
    //             'arrival_date' => $request->arrival_date,
    //             'status' => 'under_review', // Changed to under_review after form submission
    //         ]);

    //         // Create profile
    //         $profile = TenantProfile::create([
    //             'tenant_id' => $tenant->id,
    //             'first_name' => $request->first_name,
    //             'middle_name' => $request->middle_name,
    //             'last_name' => $request->last_name,
    //             'phone' => $request->phone,
    //             'country_code' => $request->country_code,
    //         ]);

    //         // Create address
    //         $address = TenantAddress::create([
    //             'tenant_id' => $tenant->id,
    //             'address' => $request->address,
    //             'city' => $request->city,
    //             'state' => $request->state,
    //             'zip' => $request->zip,
    //             'country' => $request->country,
    //         ]);

    //         // Create employment histories (multiple support)
    //         $employmentHistories = [];
    //         if ($request->has('employment_histories') && is_array($request->employment_histories)) {
    //             foreach ($request->employment_histories as $empData) {
    //                 $employment = TenantEmploymentHistory::create([
    //                     'tenant_id' => $tenant->id,
    //                     'employment_status' => $empData['employment_status'] ?? null,
    //                     'employer' => $empData['employer'] ?? null,
    //                     'title' => $empData['title'] ?? null,
    //                     'contact_person_name' => $empData['contact_person_name'] ?? null,
    //                     'contact_email' => $empData['contact_email'] ?? null,
    //                     'contact_phone' => $empData['contact_phone'] ?? null,
    //                     'is_current_working' => $empData['is_current_working'] ?? false,
    //                     'school' => $empData['school'] ?? null,
    //                     'start_date' => $empData['start_date'] ?? null,
    //                     'graduation_date' => $empData['graduation_date'] ?? null,
    //                 ]);
    //                 $employmentHistories[] = $employment;
    //             }
    //         } else {
    //             // Single employment history (backward compatibility)
    //             $employment = TenantEmploymentHistory::create([
    //                 'tenant_id' => $tenant->id,
    //                 'employment_status' => $request->employment_status,
    //                 'employer' => $request->employer,
    //                 'title' => $request->title,
    //                 'contact_person_name' => $request->contact_person_name,
    //                 'contact_email' => $request->contact_email,
    //                 'contact_phone' => $request->contact_phone,
    //                 'is_current_working' => $request->is_current_working ?? false,
    //                 'school' => $request->school,
    //                 'start_date' => $request->start_date,
    //                 'graduation_date' => $request->graduation_date,
    //             ]);
    //             $employmentHistories[] = $employment;
    //         }

    //         // Create emergency contacts (multiple support)
    //         $emergencyContacts = [];
    //         if ($request->has('emergency_contacts') && is_array($request->emergency_contacts)) {
    //             foreach ($request->emergency_contacts as $contactData) {
    //                 $contact = TenantEmergencyContact::create([
    //                     'tenant_id' => $tenant->id,
    //                     'name' => $contactData['name'],
    //                     'phone' => $contactData['phone'],
    //                     'email' => $contactData['email'] ?? null,
    //                     'relationship' => $contactData['relationship'] ?? null,
    //                 ]);
    //                 $emergencyContacts[] = $contact;
    //             }
    //         } else {
    //             // Single emergency contact (backward compatibility)
    //             $contact = TenantEmergencyContact::create([
    //                 'tenant_id' => $tenant->id,
    //                 'name' => $request->emergency_name,
    //                 'phone' => $request->emergency_phone,
    //                 'email' => $request->emergency_email,
    //                 'relationship' => $request->emergency_relationship,
    //             ]);
    //             $emergencyContacts[] = $contact;
    //         }

    //         // Handle document uploads
    //         $documentTypes = ['passport', 'visa', 'id_front', 'id_back'];
    //         $uploadedDocuments = [];

    //         foreach ($documentTypes as $docType) {
    //             if ($request->hasFile($docType)) {
    //                 $file = $request->file($docType);
    //                 $path = $file->store("tenant_documents/{$tenant->id}", 'private');

    //                 $document = TenantDocument::create([
    //                     'tenant_id' => $tenant->id,
    //                     'document_type' => $docType,
    //                     'file_path' => $path,
    //                     'file_original_name' => $file->getClientOriginalName(),
    //                     'mime_type' => $file->getMimeType(),
    //                     'file_size' => $file->getSize(),
    //                 ]);

    //                 $uploadedDocuments[] = $document;
    //             }
    //         }

    //         // Handle other documents (multiple files)
    //         if ($request->hasFile('other')) {
    //             foreach ($request->file('other') as $file) {
    //                 $path = $file->store("tenant_documents/{$tenant->id}", 'private');

    //                 $document = TenantDocument::create([
    //                     'tenant_id' => $tenant->id,
    //                     'document_type' => 'other',
    //                     'file_path' => $path,
    //                     'file_original_name' => $file->getClientOriginalName(),
    //                     'mime_type' => $file->getMimeType(),
    //                     'file_size' => $file->getSize(),
    //                 ]);

    //                 $uploadedDocuments[] = $document;
    //             }
    //         }

    //         // Generate new approval token for admin review
    //         $tenant->generateApprovalToken();

    //         // Send email to admin about form submission for review
    //         try {
    //             Mail::to(config('mail.admin_email'))
    //                 ->send(new TenantFormSubmittedMail($tenant));
    //         } catch (Exception $mailError) {
    //             Log::error('Failed to send admin notification email: ' . $mailError->getMessage());
    //         }

    //         // Short delay to avoid rate limiting
    //         sleep(1);

    //         // Send success mail to tenant
    //         try {
    //             Mail::to($tenant->email)
    //                 ->send(new TenantFormSubmissionSuccessMail($tenant));
    //         } catch (Exception $mailError) {
    //             Log::error('Failed to send success email to tenant: ' . $mailError->getMessage());
    //         }

    //         DB::commit();

    //         return $this->success([
    //             'tenant' => [
    //                 'id' => $tenant->id,
    //                 'email' => $tenant->email,
    //                 'status' => $tenant->status,
    //                 'approval_token' => $tenant->approval_token,
    //                 'profile' => $profile,
    //                 'address' => $address,
    //                 'employment_histories' => $employmentHistories,
    //                 'emergency_contacts' => $emergencyContacts,
    //                 'documents_count' => count($uploadedDocuments)
    //             ]
    //         ], 'Your application has been submitted successfully. We will review it and get back to you soon.', 201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error('Tenant form submission error: ' . $e->getMessage());
    //         return $this->error([], 'Failed to submit application. Please try again.', 500);
    //     }
    // }
}
