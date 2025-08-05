<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Http\Resources\CarResource;
use App\Services\CarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CarController extends Controller
{
    protected CarService $carService;

    public function __construct(CarService $carService)
    {
        $this->carService = $carService;
    }

    public function addCar(int $showroom_id, CarRequest $carRequest): JsonResponse
    {
        $validated = $carRequest->validated();
        $validated['showroom_id'] = $showroom_id;

        $result = $this->carService->store($showroom_id, $validated);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
            'car' => new CarResource($result['car']),
        ], 201);
    }

    public function updateCar(int $carID, CarRequest $carRequest): JsonResponse
    {
        $validatedData = $carRequest->validated();

        $result = $this->carService->updateCar($carID, $validatedData);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
            'car' => new CarResource($result['car']),
        ]);
    }

    public function deleteCar(int $showroom_id, int $car_id): JsonResponse
    {
        $deleted = $this->carService->deleteCar($car_id, $showroom_id);

        if (!$deleted['status']) {
            return response()->json(['message' => $deleted['message']], 404);
        }

        return response()->json([
            'status' => true,
            'message' => $deleted['message'],
        ]);
    }

    public function getCar(int $showroom_id, int $car_id): JsonResponse
    {
        $car = $this->carService->getCarById($showroom_id, $car_id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        return response()->json([
            'status' => true,
            'car' => new CarResource($car),
        ]);
    }

    public function listCarsByShowroom(int $showroom_id): JsonResponse
    {
        $cars = $this->carService->listCars($showroom_id);

        return response()->json([
            'status' => true,
            'cars' => CarResource::collection($cars),
        ]);
    }

    public function changeCarStatus(int $carID, string $status): JsonResponse
    {
        $result = $this->carService->changeStatus($carID, $status);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
        ]);
    }
}
