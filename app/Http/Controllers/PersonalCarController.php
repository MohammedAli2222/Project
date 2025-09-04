<?php

namespace App\Http\Controllers;

use App\Http\Resources\CarResource;
use App\Services\PersonalCarService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class PersonalCarController extends Controller
{
    public function __construct(
        private PersonalCarService $service
    ) {}

    public function index(Request $request)
    {
        $cars = $this->service->list([
            'user_id'   => $request->query('user_id'),
            'status'    => $request->query('status'),
            'brand'     => $request->query('brand'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
        ]);

        return response()->json([
            'status' => true,
            'cars' => CarResource::collection($cars),
        ]);
    }


    public function show(int $id)
    {
        $car = app(\App\Repositories\PersonalCarRepository::class)->getById($id);
        abort_unless($car, 404, 'Personal car not found');
        return response()->json($car);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request, true);
        $userId = $request->user()->id ?? $validated['user_id']; // مرونة لو تستعمل Sanctum/JWT

        $car = $this->service->create($validated, (int)$userId);
        return response()->json($car, 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $this->validatePayload($request, false);
        $userId = $request->user()->id ?? $validated['user_id'] ?? null;
        abort_unless($userId, 422, 'user_id required');

        $car = $this->service->update($id, $validated, (int)$userId);
        return response()->json($car);
    }

    public function destroy(Request $request, int $id)
    {
        $userId = $request->user()->id ?? (int)$request->input('user_id');
        abort_unless($userId, 422, 'user_id required');

        $this->service->delete($id, $userId);
        return response()->json(['message' => 'Deleted']);
    }

    private function validatePayload(Request $request, bool $isCreate): array
    {
        $base = [
            'user_id'   => ['sometimes', 'integer', 'exists:users,id'],

            'condition' => ['required_if:isCreate,true', Rule::in(['new', 'used'])],
            'vin'       => [$isCreate ? 'required' : 'sometimes', 'string', 'size:17', $isCreate ? 'unique:personal_cars,vin' : ''],
            'available_status' => ['sometimes', Rule::in(['available', 'reserved', 'sold', 'rented'])],
            'is_rentable' => ['sometimes', 'boolean'],
            'rental_cost_per_hour' => ['nullable', 'numeric', 'min:0'],

            'name'      => [$isCreate ? 'required' : 'sometimes', 'string'],
            'brand'     => [$isCreate ? 'required' : 'sometimes', 'string'],
            'model'     => [$isCreate ? 'required' : 'sometimes', 'string'],
            'gear_box'  => [$isCreate ? 'required' : 'sometimes', Rule::in(['manual', 'automatic', 'cvt'])],
            'year'      => [$isCreate ? 'required' : 'sometimes', 'digits:4'],
            'fuel_type' => [$isCreate ? 'required' : 'sometimes', Rule::in(['petrol', 'diesel', 'hybrid', 'electric'])],
            'body_type' => [$isCreate ? 'required' : 'sometimes', Rule::in(['sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'truck'])],
            'color'     => [$isCreate ? 'required' : 'sometimes', Rule::in([
                'White',
                'Grey',
                'Black',
                'Light Red',
                'Red',
                'Dark Red',
                'Light Blue',
                'Blue',
                'Dark Blue',
                'Light Green',
                'Green',
                'Dark Green',
                'Light Pink',
                'Pink',
                'Dark Pink',
                'Light Purple',
                'Purple',
                'Dark Purple',
                'Light Yellow',
                'Yellow',
                'Dark Yellow',
                'Beige',
                'Light Orange',
                'Orange',
                'Brown'
            ])],

            'engine_type' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'cylinders'   => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1'],
            'horse_power' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1'],

            'price'       => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'currency'    => [$isCreate ? 'required' : 'sometimes', Rule::in(['SYR', 'USD'])],
            'negotiable'  => ['sometimes', 'boolean'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],

            'images' => ['sometimes', 'array'],
            'images.*' => ['file', 'image', 'max:5120'], // 5MB
            'main_image_index' => ['nullable', 'integer', 'min:0'],
        ];

        // hack صغير لأن required_if لا يعرف $isCreate خارج rules
        if ($isCreate) $request->merge(['isCreate' => true]);

        return $request->validate($base);
    }
}
