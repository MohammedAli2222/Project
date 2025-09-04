<?php

namespace App\Repositories;

use App\Models\PersonalCar;
use App\Models\PersonalCarInfo;
use App\Models\PersonalCarImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PersonalCarRepository
{
    public function createCar(array $carData): PersonalCar
    {
        return PersonalCar::create($carData);
    }

    public function createInfo(int $personalCarId, array $infoData): PersonalCarInfo
    {
        $infoData['personal_car_id'] = $personalCarId;
        return PersonalCarInfo::create($infoData);
    }

    /**
     * @param int   $personalCarId
     * @param array $pathsAndMain  مثال: [['path' => 'storage/...jpg', 'is_main' => true], ...]
     * @return PersonalCarImage[]
     */
    public function addImages(int $personalCarId, array $pathsAndMain): array
    {
        // اجعل صورة رئيسية واحدة فقط
        $hasMain = false;
        foreach ($pathsAndMain as &$row) {
            $row['is_main'] = !$hasMain && !empty($row['is_main']);
            if ($row['is_main']) $hasMain = true;
        }
        unset($row);

        $created = [];
        foreach ($pathsAndMain as $img) {
            $created[] = PersonalCarImage::create([
                'personal_car_id' => $personalCarId,
                'image_path'      => $img['path'],
                'is_main'         => (bool)$img['is_main'],
            ]);
        }
        return $created;
    }

    public function getById(int $id): ?PersonalCar
    {
        return PersonalCar::with(['info', 'images', 'mainImage'])->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $q = PersonalCar::with(['info', 'mainImage']);

        if (!empty($filters['user_id'])) {
            $q->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $q->where('available_status', $filters['status']);
        }
        if (!empty($filters['brand'])) {
            $q->whereHas('info', fn($qq) => $qq->where('brand', $filters['brand']));
        }
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $q->whereHas('info', function ($qq) use ($filters) {
                if (!empty($filters['min_price'])) $qq->where('price', '>=', $filters['min_price']);
                if (!empty($filters['max_price'])) $qq->where('price', '<=', $filters['max_price']);
            });
        }

        return $q->latest()->paginate($perPage);
    }

    public function updateCar(PersonalCar $car, array $carData): PersonalCar
    {
        $car->update($carData);
        return $car->fresh(['info', 'images', 'mainImage']);
    }

    public function updateInfo(PersonalCar $car, array $infoData): PersonalCarInfo
    {
        if ($car->info) {
            $car->info->update($infoData);
            return $car->info->fresh();
        }
        return $this->createInfo($car->id, $infoData);
    }

    public function replaceImages(PersonalCar $car, array $pathsAndMain): array
    {
        $car->images()->delete();
        return $this->addImages($car->id, $pathsAndMain);
    }

    public function delete(PersonalCar $car): void
    {
        $car->delete(); // cascade على info, images
    }
}
