<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'user_name'        => 'required|string|max:255',
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'required|string|unique:users,phone',
            'password'         => 'required|min:8|max:20',
            'profile_picture'  => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_id'          => 'sometimes|integer'

        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first_name field is required.',
            'first_name.string' => 'The first_name must be a string.',
            'first_name.max' => 'The first_name may not be greater than 255 characters.',

            'last_name.required' => 'The last_name field is required.',
            'last_name.string' => 'The last_name must be a string.',
            'last_name.max' => 'The last_name may not be greater than 255 characters.',


            'user_name.required' => 'The user_name field is required.',
            'user_name.string' => 'The user_name must be a string.',
            'user_name.max' => 'The user_name may not be greater than 255 characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'Use an Email valid .',

            'phone.required' => 'The phone number is required.',
            'phone.string' => 'The phone number must be a string.',
            'phone.unique' => 'This phone number is already in use.',

            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.max' => 'The password may not be greater than 20 characters.',

            'profile_picture.image' => 'The profile picture must be an image.',
            'profile_picture.mimes' => 'The profile picture must be a file of type: jpeg, png, jpg, gif.',
            'profile_picture.max' => 'The profile picture may not be larger than 2MB.',
        ];
    }
}
