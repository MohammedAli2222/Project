<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalCarRequest;
use App\Http\Resources\PersonalCarResource;
use App\Services\PersonalCarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PersonalCarController extends Controller
{
    protected PersonalCarService $personalCarService;

    public function __construct(PersonalCarService $personalCarService)
    {
        $this->personalCarService = $personalCarService;
    }

    public function addPersonalCar(PersonalCarRequest $personalCarRequest): JsonResponse
    {
        $validated = $personalCarRequest->validated();

        $result = $this->personalCarService->store($validated);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
            'car' => new PersonalCarResource($result['car']),
        ], 201);
    }

    public function updatePersonalCar(int $carID, PersonalCarRequest $request): JsonResponse
    {
        $result = $this->personalCarService->updateCar($carID, $request->validated());

        if ($result['status']) {
            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'car' => new PersonalCarResource($result['car'])
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message']
        ], 400);
    }

    public function deletePersonalCar(int $car_id): JsonResponse
    {
        $deleted = $this->personalCarService->deleteCar($car_id);

        if (!$deleted['status']) {
            return response()->json(['message' => $deleted['message']], 404);
        }

        return response()->json([
            'status' => true,
            'message' => $deleted['message'],
        ]);
    }

    public function getPersonalCar(int $car_id): JsonResponse
    {
        $car = $this->personalCarService->getCarById($car_id);

        if (!$car['status']) {
            return response()->json(['message' => 'Personal car not found'], 404);
        }

        return response()->json([
            'status' => true,
            'car' => new PersonalCarResource($car['car']),
        ]);
    }

    public function listPersonalCars(): JsonResponse
    {
        $cars = $this->personalCarService->listCars();

        return response()->json([
            'status' => true,
            'cars' => PersonalCarResource::collection($cars['cars']),
        ]);
    }

    public function changePersonalCarStatus(int $carID, string $status): JsonResponse
    {
        $result = $this->personalCarService->changeStatus($carID, $status);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
        ]);
    }

    public function getRandomPersonalCars()
    {
        $cars = $this->personalCarService->getRandomCarsForHomepage();

        return response()->json([
            'status' => true,
            'data' => PersonalCarResource::collection($cars),
        ]);
    }

    public function allPersonalCars()
    {
        $result = $this->personalCarService->getAllCars();
        return response()->json($result);
    }

    public function getUserPersonalCars()
    {
        $userId = auth()->id();
        $result = $this->personalCarService->getCarsByUserId($userId);

        return response()->json($result);
    }
}
