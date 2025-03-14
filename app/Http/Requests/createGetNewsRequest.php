<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class createGetNewsRequest extends FormRequest
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
            'source' => 'required|in:guardian,newsorg,newsapi,nytimes|max:50',
        ];
    }

    public function messages()
    {
        return [
            'source.required' => 'The source is required.',
            'source.max' => 'The source cannot be longer than 50 characters.',
        ];
    }

}
