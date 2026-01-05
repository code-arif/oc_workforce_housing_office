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
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',

            // Address
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100',

            // Employment
            'employment_status' => 'required|string|in:employed,student,unemployed,self-employed',
            'employer' => 'required_if:employment_status,employed,self-employed|nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'is_current_working' => 'nullable|boolean',
            'school' => 'required_if:employment_status,student|nullable|string|max:255',
            'start_date' => 'nullable|date',
            'graduation_date' => 'nullable|date|after:start_date',

            // Emergency Contact
            'emergency_name' => 'required|string|max:255',
            'emergency_phone' => 'required|string|max:20',
            'emergency_email' => 'nullable|email',
            'emergency_relationship' => 'required|string|max:100',

            // Documents
            'passport' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'visa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_front' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_back' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'other.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Dates
            'move_in_date' => 'required|date|after:today',
            'arrival_date' => 'nullable|date',
        ];
    }
}
