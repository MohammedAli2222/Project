<?php

namespace App\Repositories;

use App\Models\PersonalCar;
use App\Models\PersonalCarInfo;
use App\Models\PersonalCarImage;
use Illuminate\Support\Facades\DB;

class PersonalCarRepository
{
    public function create(array $data): PersonalCar
    {
        return PersonalCar::create($data);
    }

    public function createInfo(int $carId, array $data): PersonalCarInfo
    {
        return PersonalCarInfo::create(array_merge($data, ['personal_car_id' => $carId]));
    }

    public function createImage(int $carId, string $imagePath, bool $isMain = false): PersonalCarImage
    {
        return PersonalCarImage::create([
            'personal_car_id' => $carId,
            'image_path' => $imagePath,
            'is_main' => $isMain,
        ]);
    }

    public function delete(int $userID, int $car_id): bool
    {
        return PersonalCar::where('user_id', $userID)
            ->where('id', $car_id)
            ->delete() > 0;
    }

    public function findCar(int $car_id)
    {
        return PersonalCar::where('id', $car_id)
            ->with(['info', 'images'])
            ->first();
    }

    public function findCarById(int $carID)
    {
        return PersonalCar::with(['info', 'images'])->find($carID);
    }

    public function listCarsByUser(int $user_id)
    {
        return PersonalCar::where('user_id', $user_id)
            ->with(['info', 'images'])
            ->get();
    }

    public function updateStatus(int $carID, string $status): bool
    {
        $car = PersonalCar::find($carID);

        if (!$car) {
            return false;
        }

        $car->available_status = $status;
        return $car->save();
    }

    public function getRandomCars(int $limit = 10)
    {
        return PersonalCar::where('available_status', 'available')
            ->inRandomOrder()
            ->limit($limit)
            ->with(['info', 'images'])
            ->get();
    }

    public function getAllCars()
    {
        return PersonalCar::with(['info', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUserId(int $userId)
    {
        return PersonalCar::with(['info', 'images'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * تحديث بيانات جدول personal_car_infos
     * إذا ما كان في record → ينشئ واحد جديد
     */
    public function updateCarInfo(int $carId, array $data): bool
    {
        $info = PersonalCarInfo::updateOrCreate(
            ['personal_car_id' => $carId],
            $data
        );

        return (bool) $info;
    }

    /**
     * تحديث بيانات جدول personal_cars
     */
    public function updateCarMain(int $carId, array $data): bool
    {
        $car = PersonalCar::find($carId);

        if ($car) {
            return $car->update($data);
        }

        return false;
    }

    public function deleteCarImages(int $carId): bool
    {
        return PersonalCarImage::where('personal_car_id', $carId)->delete() > 0;
    }
}
