<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CmsRequest extends FormRequest
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
            'page'              => 'nullable|string|max:100',
            'section'           => 'nullable|string|max:100',
            'name'              => 'nullable|string|max:50',
            'slug'              => 'nullable|string|max:150',
            'title'             => 'nullable|string|max:255',
            'sub_title'         => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'sub_description'   => 'nullable|string',

            // migration e longText, tai string/URL/path dhora holo
            'bg'                => 'nullable|string',
            'image'             => 'nullable',

            'btn_text'          => 'nullable|string|max:50',
            'btn_link'          => 'nullable|string|max:255',
            'btn_color'         => 'nullable|string|max:50',

            'metadata'          => 'nullable|array',

            'status'            => 'nullable|in:active,inactive',

            'email'             => 'nullable|email|max:100',
            'phone'             => 'nullable|string|max:30',
            'address'           => 'nullable|string|max:255',
            'slogan'            => 'nullable|string|max:255',
            'business_name'     => 'nullable|string|max:255',
        ];
    }
}
