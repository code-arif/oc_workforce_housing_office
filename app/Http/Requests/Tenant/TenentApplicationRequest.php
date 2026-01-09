<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Profile
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',

            // Address
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',

            // Dates
            'move_in_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',

            // Employment (Single - Backward compatibility)
            'employment_status' => 'nullable|string|in:employed,student,unemployed,self-employed',
            'employer' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'is_current_working' => 'nullable|boolean',
            'school' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'graduation_date' => 'nullable|date|after:start_date',

            // Employment (Multiple - Array support)
            'employment_histories' => 'nullable|array',
            'employment_histories.*.employment_status' => 'required|string|in:employed,student,unemployed,self-employed',
            'employment_histories.*.employer' => 'nullable|string|max:255',
            'employment_histories.*.title' => 'nullable|string|max:255',
            'employment_histories.*.contact_person_name' => 'nullable|string|max:255',
            'employment_histories.*.contact_email' => 'nullable|email',
            'employment_histories.*.contact_phone' => 'nullable|string|max:20',
            'employment_histories.*.is_current_working' => 'nullable|boolean',
            'employment_histories.*.school' => 'nullable|string|max:255',
            'employment_histories.*.start_date' => 'nullable|date',
            'employment_histories.*.graduation_date' => 'nullable|date|after:employment_histories.*.start_date',

            // Emergency Contact (Single - Backward compatibility)
            'emergency_name' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_email' => 'nullable|email',
            'emergency_relationship' => 'nullable|string|max:100',

            // Emergency Contacts (Multiple - Array support)
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.name' => 'required|string|max:255',
            'emergency_contacts.*.phone' => 'required|string|max:20',
            'emergency_contacts.*.email' => 'nullable|email',
            'emergency_contacts.*.relationship' => 'nullable|string|max:100',

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
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'gender.required' => 'Gender is required.',
            'address.required' => 'Address is required.',
            'phone.required' => 'Phone number is required.',

            'employment_histories.*.employment_status.required' => 'Employment status is required for each employment history.',
            'employment_histories.*.graduation_date.after' => 'Graduation date must be after start date.',

            'emergency_contacts.*.name.required' => 'Emergency contact name is required.',
            'emergency_contacts.*.phone.required' => 'Emergency contact phone is required.',

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
        // Convert is_current_working to boolean if present
        if ($this->has('is_current_working')) {
            $this->merge([
                'is_current_working' => filter_var($this->is_current_working, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Handle employment histories array
        if ($this->has('employment_histories')) {
            $histories = $this->employment_histories;
            if (is_array($histories)) {
                foreach ($histories as $key => $history) {
                    if (isset($history['is_current_working'])) {
                        $histories[$key]['is_current_working'] = filter_var($history['is_current_working'], FILTER_VALIDATE_BOOLEAN);
                    }
                }
                $this->merge(['employment_histories' => $histories]);
            }
        }
    }
}
