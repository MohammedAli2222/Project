<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

        $userId = $this->user()->id;

        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';
        
        $showroom_id = $this->route('showroom');


        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('showrooms')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId)
                        ->where('location', $this->location);
                })->ignore($showroom_id),
            ],
            'location' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^(\d{10})$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The showroom name is required.',
            'name.string' => 'The showroom name must be a string.',
            'name.max' => 'The showroom name may not be greater than :max characters.',
            'name.unique' => 'A showroom with this name and location already exists for this user.',

            'location.required' => 'The showroom location is required.',
            'location.string' => 'The showroom location must be a string.',
            'location.max' => 'The showroom location may not be greater than :max characters.',

            'logo.image' => 'The logo must be an image.',
            'logo.mimes' => 'The logo must be a file of type: :values.',
            'logo.max' => 'The logo may not be larger than :max kilobytes.',

            'phone.string' => 'The phone number must be a string.',
            'phone.max' => 'The phone number may not be greater than :max digits.',
            'phone.regex' => 'The phone number must be exactly 10 digits.',
        ];
    }
}
