<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TenentApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     * Returns JSON response for API requests.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Applicant Profile
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'country_of_origin' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date|before:today',

            // Dates
            'arrival_date' => 'nullable|date',
            'departure_date' => 'nullable|date|after_or_equal:arrival_date',

            // Property preference
            'property_id' => 'nullable|integer|exists:properties,id',

            // Notes
            'notes' => 'nullable|string|max:2000',

            // Employer Information (Single - Backward compatibility)
            // 'company_name' => 'nullable|string|max:255',
            // 'company_address' => 'nullable|string|max:500',
            // 'industry' => 'nullable|string|max:255',
            // 'job_title' => 'nullable|string|max:255',
            // 'employer_contact_person_name' => 'nullable|string|max:255',
            // 'employer_contact_person_phone' => 'nullable|string|max:20',
            // 'employer_contact_person_email' => 'nullable|email',

            // Employer Information (Multiple - Array support)
            'employment_histories' => 'nullable|array',
            'employment_histories.*.company_name' => 'nullable|string|max:255',
            'employment_histories.*.company_address' => 'nullable|string|max:500',
            'employment_histories.*.industry' => 'nullable|string|max:255',
            'employment_histories.*.job_title' => 'nullable|string|max:255',
            'employment_histories.*.employer_contact_person_name' => 'nullable|string|max:255',
            'employment_histories.*.employer_contact_person_phone' => 'nullable|string|max:20',
            'employment_histories.*.employer_contact_person_email' => 'nullable|email',

            // Sponsor Information
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_city' => 'nullable|string|max:100',
            'sponsor_state' => 'nullable|string|max:100',
            'sponsor_zipcode' => 'nullable|string|max:20',
            'sponsor_country' => 'nullable|string|max:100',
            'sponsor_phone' => 'nullable|string|max:20',
            'sponsor_email' => 'nullable|email',
            'sponsor_relationship' => 'nullable|string|max:100',
            'is_j1_sponsor' => 'nullable|string|in:yes,no',

            // Documents
            'passport' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'visa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_front' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_back' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'other' => 'nullable|array',
            'other.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required' => 'Phone number is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'departure_date.after_or_equal' => 'Departure date must be on or after arrival date.',
            'property_id.exists' => 'Selected property does not exist.',

            'employment_histories.*.employer_contact_person_email.email' => 'Please provide a valid employer contact email.',
            'sponsor_email.email' => 'Please provide a valid sponsor email.',

            'passport.mimes' => 'Passport must be a PDF, JPG, JPEG, or PNG file.',
            'visa.mimes' => 'Visa must be a PDF, JPG, JPEG, or PNG file.',
            'id_front.mimes' => 'ID front must be a PDF, JPG, JPEG, or PNG file.',
            'id_back.mimes' => 'ID back must be a PDF, JPG, JPEG, or PNG file.',
            'other.*.mimes' => 'Other documents must be PDF, JPG, JPEG, or PNG files.',

            '*.max' => 'File size must not exceed 5MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Normalize is_j1_sponsor to string
        if ($this->has('is_j1_sponsor')) {
            $value = $this->is_j1_sponsor;
            if (is_bool($value)) {
                $this->merge([
                    'is_j1_sponsor' => $value ? 'yes' : 'no'
                ]);
            }
        }
    }
}
