<?php

namespace App\Repositories;

use App\Models\Car;
use App\Models\car_financial_info;
use App\Models\car_general_info;
use App\Models\car_images;
use App\Models\car_technical_spec;

class CarRepository
{
    public function create(array $data): Car
    {
        return Car::create($data);
    }

    public function createGeneralInfo(int $carId, array $data)
    {
        return car_general_info::create([
            'car_id' => $carId,
            'name' => $data['name'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'gear_box' => $data['gear_box'],
            'year' => $data['year'],
            'fuel_type' => $data['fuel_type'],
            'body_type' => $data['body_type'],
            'vin' => $data['vin'],
            'condition' => $data['condition']
        ]);
    }
    public function createFinancialInfo(int $carId, array $data)
    {
        return car_financial_info::create([
            'car_id' => $carId,
            'price' => $data['price'],
            'currency' => $data['currency'] ?? 'SYP',
            'negotiable' => $data['negotiable'] ?? false,
            'discount_percentage' => $data['discount_percentage'] ?? null,
            'discount_amount' => $data['discount_amount'] ?? null,
        ]);
    }
    public function createTechnicalSpec(int $carId, array $data)
    {
        return car_technical_spec::create([
            'car_id' => $carId,
            'horse_power' => $data['horse_power'],
            'engine_type' => $data['engine_type'],
            'cylinders' => $data['cylinders'],
        ]);
    }
    ////////////////////////////////

    public function updateCarGeneralInfo(int $carId, array $data): bool
    {
        return car_general_info::where('car_id', $carId)->update($data) > 0;
    }

    public function updateCarFinancialInfo(int $carId, array $data): bool
    {
        return car_financial_info::where('car_id', $carId)->update($data) > 0;
    }

    public function updateCarTechnicalSpec(int $carId, array $data): bool
    {
        return car_technical_spec::where('car_id', $carId)->update($data) > 0;
    }

    public function updateCarMainInfo(int $carId, array $data): bool
    {
        return Car::where('id', $carId)->update($data) > 0;
    }

    public function deleteCarImages(int $carId): bool
    {
        return car_images::where('car_id', $carId)->delete() > 0;
    }

    public function createImage(int $carId, string $imagePath, bool $isMain = false): car_images
    {
        return car_images::create([
            'car_id' => $carId,
            'image_path' => $imagePath,
            'is_main' => $isMain,
        ]);
    }

    ////////////////////////
    // public function createImage(int $carId, string $imagePath, bool $isMain = false)
    // {
    //     return car_images::create([
    //         'car_id' => $carId,
    //         'image_path' => $imagePath,
    //         'is_main' => $isMain,
    //     ]);
    // }
    public function delete(int $userID, int $showroom_id, int $car_id): bool
    {
        return Car::where('user_id', $userID)
            ->where('showroom_id', $showroom_id)
            ->where('id', $car_id)
            ->delete() > 0;
    }
    public function findCar(int $showroom_id, int $car_id)
    {
        return Car::where('id', $car_id)
            ->where('showroom_id', $showroom_id)
            ->with(['generalInfo', 'financialInfo', 'technicalSpecs', 'images'])
            ->first();
    }
    public function listCarsInShowroom(int $showroom_id)
    {
        return Car::where('showroom_id', $showroom_id)
            ->with(['generalInfo', 'financialInfo', 'technicalSpecs', 'images'])
            ->get();
    }
    public function findCarById(int $carID)
    {
        return Car::with(['generalInfo', 'financialInfo', 'technicalSpecs', 'images'])->find($carID);
    }

    public function updateStatus(int $carID, string $status): bool
    {
        $car = Car::find($carID);

        if (!$car) {
            return false;
        }

        $car->available_status = $status;

        return $car->save();
    }
    public function getRandomCarsFromMultipleShowrooms(int $limit = 10)
    {
        return Car::with('showroom')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
