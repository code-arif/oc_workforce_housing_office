<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyTypeRequest extends FormRequest
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
        $propertyTypeId = $this->route('id') ?? null;

        return [
            'name' => 'required|string|max:255|unique:property_types,name,' . $propertyTypeId,
            'slug' => 'nullable|string|unique:property_types,slug,' . $propertyTypeId,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Property type name is required.',
            'name.unique' => 'This property type name already exists.',
            'name.string' => 'Property type name must be a string.',
            'name.max' => 'Property type name must not exceed 255 characters.',
            'slug.string' => 'Slug must be a string.',
            'slug.unique' => 'This slug already exists.',
            'description.string' => 'Description must be a string.',
            'is_active.boolean' => 'Active status must be a boolean.',
        ];
    }
}
