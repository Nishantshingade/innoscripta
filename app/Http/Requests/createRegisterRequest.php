<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class createRegisterRequest extends FormRequest
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
            'name' => 'required|regex:/^[A-Za-z\s\']+$/|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, and apostrophes.',
            'email.unique' => 'This email is already taken.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
