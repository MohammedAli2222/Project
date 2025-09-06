<?php

namespace App\Services;

use App\Models\PersonalCar;
use App\Repositories\PersonalCarRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;



class PersonalCarService
{
    protected PersonalCarRepository $personalCarRepository;

    public function __construct(PersonalCarRepository $personalCarRepository)
    {
        $this->personalCarRepository = $personalCarRepository;
    }

    public function store(array $data): array
    {
        $userID = auth()->id();

        DB::beginTransaction();

        try {
            // إنشاء السيارة الشخصية
            $car = $this->personalCarRepository->create([
                'user_id' => $userID,
                'vin' => $data['vin'],
                'condition' => $data['condition'],
                'available_status' => $data['available_status'] ?? 'available',
                'is_rentable' => (bool)($data['is_rentable'] ?? false),
                'rental_cost_per_hour' => $data['rental_cost_per_hour'] ?? null,
            ]);

            // إنشاء المعلومات في جدول info
            $this->personalCarRepository->createInfo($car->id, [
                'name' => $data['name'],
                'brand' => $data['brand'],
                'model' => $data['model'],
                'gear_box' => $data['gear_box'],
                'year' => $data['year'],
                'fuel_type' => $data['fuel_type'],
                'body_type' => $data['body_type'],
                'color' => $data['color'],
                'engine_type' => $data['engine_type'],
                'cylinders' => $data['cylinders'],
                'horse_power' => $data['horse_power'],
                'mileage' => $data['mileage'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'currency' => $data['currency'],
                'negotiable' => (bool)($data['negotiable'] ?? false),
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? null,
            ]);

            // معالجة الصور
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $path = $image->store('personal_cars', 'public');
                        $path = 'storage/' . $path;

                        $isMain = (isset($data['main_image_index']) && $data['main_image_index'] == $index);
                        $this->personalCarRepository->createImage($car->id, $path, $isMain);
                    }
                }
            }

            DB::commit();

            $car->load(['info', 'images']);

            return [
                'status' => true,
                'message' => 'Personal car added successfully',
                'car' => $car,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to add personal car: ' . $e->getMessage(),
            ];
        }
    }

    public function updateCar(int $carID, array $data): array
    {
        $userID = auth()->id();

        // 1. استخدام findCarById بدلاً من findCar
        $car = $this->personalCarRepository->findCarById($carID);

        if (!$car) {
            return [
                'status' => false,
                'message' => 'Personal car not found.'
            ];
        }

        if ($car->user_id !== $userID) {
            return [
                'status' => false,
                'message' => 'Unauthorized.'
            ];
        }

        DB::beginTransaction();

        try {
            // تحديث personal_cars
            $carMainFields = [
                'vin',
                'condition',
                'available_status',
                'is_rentable',
                'rental_cost_per_hour'
            ];

            $carMainData = [];
            foreach ($carMainFields as $field) {
                if (isset($data[$field]) || array_key_exists($field, $data)) {
                    $carMainData[$field] = $data[$field];
                }
            }

            if (!empty($carMainData)) {
                $this->personalCarRepository->updateCarMain($carID, $carMainData);
            }

            // تحديث personal_car_infos
            $infoFields = [
                'name',
                'brand',
                'model',
                'gear_box',
                'year',
                'fuel_type',
                'body_type',
                'color',
                'engine_type',
                'cylinders',
                'horse_power',
                'mileage',
                'description',
                'price',
                'currency',
                'negotiable',
                'discount_percentage',
                'discount_amount'
            ];

            $infoData = [];
            foreach ($infoFields as $field) {
                if (isset($data[$field]) || array_key_exists($field, $data)) {
                    $infoData[$field] = $data[$field];
                }
            }

            if (!empty($infoData)) {
                $this->personalCarRepository->updateCarInfo($carID, $infoData);
            }

            // معالجة الصور
            if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {
                foreach ($car->images as $image) {
                    $fullPath = public_path($image->image_path);
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
                $this->personalCarRepository->deleteCarImages($carID);

                $mainImageIndex = $data['main_image_index'] ?? 0;
                foreach ($data['images'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $path = $image->store('personal_cars', 'public');
                        $relativePath = 'storage/' . $path;

                        $isMain = ($mainImageIndex == $index);
                        $this->personalCarRepository->createImage($carID, $relativePath, $isMain);
                    }
                }
            }

            DB::commit();

            $car->refresh()->load(['info', 'images']);

            return [
                'status' => true,
                'message' => 'Personal car updated successfully.',
                'car' => $car,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to update personal car: ' . $e->getMessage(),
            ];
        }
    }



    public function deleteCar(int $car_id): array
    {
        $userID = auth()->id();

        $deleted = $this->personalCarRepository->delete($userID, $car_id);

        if ($deleted) {
            return [
                'status' => true,
                'message' => 'Personal car deleted successfully',
            ];
        }

        return [
            'status' => false,
            'message' => 'Personal car not found or you do not have permission',
        ];
    }

    public function getCarById(int $car_id): array
    {
        $car = $this->personalCarRepository->findCar($car_id);

        if (!$car) {
            return [
                'status' => false,
                'message' => 'Personal car not found'
            ];
        }

        return [
            'status' => true,
            'car' => $car
        ];
    }

    public function listCars(): array
    {
        $userID = auth()->id();
        $cars = $this->personalCarRepository->listCarsByUser($userID);

        return [
            'status' => true,
            'cars' => $cars
        ];
    }

    public function changeStatus(int $carID, string $status): array
    {
        $car = $this->personalCarRepository->findCarById($carID);

        if (!$car) {
            return [
                'status' => false,
                'message' => 'Personal car not found.'
            ];
        }

        $userID = auth()->id();

        if ($car->user_id !== $userID) {
            return [
                'status' => false,
                'message' => 'Unauthorized.'
            ];
        }

        $allowedStatuses = ['available', 'sold', 'reserved', 'rented'];

        if (!in_array($status, $allowedStatuses)) {
            return [
                'status' => false,
                'message' => 'Invalid status.'
            ];
        }

        $updated = $this->personalCarRepository->updateStatus($carID, $status);

        if ($updated) {
            return [
                'status' => true,
                'message' => 'Status updated successfully.'
            ];
        }

        return [
            'status' => false,
            'message' => 'Failed to update status.'
        ];
    }

    public function getRandomCarsForHomepage(int $limit = 10)
    {
        return $this->personalCarRepository->getRandomCars($limit);
    }

    public function getAllCars(): array
    {
        $cars = $this->personalCarRepository->getAllCars();

        return [
            'status' => true,
            'cars' => $cars,
        ];
    }

    public function getCarsByUserId(int $userId)
    {
        try {
            $cars = $this->personalCarRepository->getByUserId($userId);
            return [
                'status' => true,
                'data' => $cars
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Failed to fetch personal cars: ' . $e->getMessage()
            ];
        }
    }
}
