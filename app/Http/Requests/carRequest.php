<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

  public function rules(): array
{
    $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';
    $carId = $this->route('car');

    return [
        'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
        'brand' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
        'model' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
        'gear_box' => [
            $isUpdate ? 'sometimes' : 'required',
            Rule::in(['manual', 'automatic', 'cvt']),
        ],
        'year' => [
            $isUpdate ? 'sometimes' : 'required',
            'integer',
            'min:1900',
            'max:' . (date('Y') + 1),
        ],
        'fuel_type' => [
            $isUpdate ? 'sometimes' : 'required',
            Rule::in(['petrol', 'diesel', 'hybrid', 'electric']),
        ],
        'body_type' => [
            $isUpdate ? 'sometimes' : 'required',
            Rule::in(['sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'truck']),
        ],
        'vin' => [
            $isUpdate ? 'sometimes' : 'required',
            'string',
            'max:17',
            'min:11',
            Rule::unique('car_general_infos')->ignore($carId),
        ],
        'condition' => [
            $isUpdate ? 'sometimes' : 'required',
            Rule::in(['new', 'used']),
        ],
        'price' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
        'currency' => ['nullable', 'string', 'max:3'],
        'negotiable' => ['sometimes', 'boolean'],
        'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        'discount_amount' => ['nullable', 'numeric', 'min:0'],
        'horse_power' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:1'],
        'engine_type' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
        'cylinders' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:1'],
        'is_rentable' => ['sometimes', 'boolean'],
        'rental_cost_per_hour' => [
            'nullable',
            'numeric',
            'min:1',
            function ($attribute, $value, $fail) {
                if (request()->input('is_rentable') && empty($value)) {
                    $fail('The rental cost per hour is required when the car is rentable.');
                }
            }
        ],
        'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        'main_image_index' => ['nullable', 'integer', 'min:0'],
    ];
}

    public function messages(): array
    {
        return [
            'name.required' => 'The car name is required.',
            'brand.required' => 'The brand is required.',
            'model.required' => 'The model is required.',
            'gear_box.required' => 'The gear box type is required.',
            'gear_box.in' => 'The selected gear box type is invalid.',
            'year.required' => 'The manufacturing year is required.',
            'year.integer' => 'The manufacturing year must be an integer.',
            'year.min' => 'The manufacturing year must be at least 1900.',
            'year.max' => 'The manufacturing year is invalid.',
            'fuel_type.required' => 'The fuel type is required.',
            'fuel_type.in' => 'The selected fuel type is invalid.',
            'body_type.required' => 'The body type is required.',
            'body_type.in' => 'The selected body type is invalid.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'currency.max' => 'The currency cannot exceed 3 characters.',
            'negotiable.boolean' => 'The negotiable value must be true or false.',
            'discount_percentage.numeric' => 'The discount percentage must be a number.',
            'discount_percentage.min' => 'The discount percentage must be at least 0.',
            'discount_percentage.max' => 'The discount percentage cannot exceed 100.',
            'discount_amount.numeric' => 'The discount amount must be a number.',
            'discount_amount.min' => 'The discount amount must be at least 0.',
            'horse_power.required' => 'Horsepower is required.',
            'horse_power.integer' => 'Horsepower must be an integer.',
            'horse_power.min' => 'Horsepower must be at least 1.',
            'engine_type.required' => 'The engine type is required.',
            'cylinders.required' => 'The number of cylinders is required.',
            'cylinders.integer' => 'The number of cylinders must be an integer.',
            'cylinders.min' => 'The number of cylinders must be at least 1.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be of type: jpeg, png, jpg, gif, svg.',
            'images.*.max' => 'Each image must not exceed 2 MB.',
            'main_image_index.integer' => 'The main image index must be an integer.',
            'main_image_index.min' => 'The main image index must be at least 0.',
            'is_rentable.boolean' => 'The is_rentable value must be true or false.',
            'rental_cost_per_hour.numeric' => 'The rental cost per hour must be a number.',
            'rental_cost_per_hour.min' => 'The rental cost per hour must be at least 1.',
            'vin.required' => 'The VIN (Vehicle Identification Number) is required.',
            'vin.string' => 'The VIN must be a string.',
            'vin.max' => 'The VIN cannot be longer than 17 characters.',
            'vin.min' => 'The VIN must be at least 11 characters.',
            'vin.unique' => 'The VIN must be unique.',
            'condition.required' => 'The car condition is required.',
            'condition.in' => 'The condition must be either new or used.',


        ];
    }
}
