<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Showroom;
use App\Repositories\CarRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;


class CarService
{
    protected CarRepository $carRepository;

    public function __construct(CarRepository $carRepository)
    {
        $this->carRepository = $carRepository;
    }
    public function store(int $showroom_id, array $data): array
    {
        $userID = auth()->id();

        $showRoom = Showroom::where('id', $showroom_id)
            ->where('user_id', $userID)
            ->first();

        if (!$showRoom) {
            return [
                'status' => false,
                'message' => 'You do not have any ShowRoom'
            ];
        }

        DB::beginTransaction();

        try {
            $car = $this->carRepository->create([
                'user_id' => $userID,
                'showroom_id' => $showroom_id,
                'available_status' => 'available',
            ]);

            $this->carRepository->createGeneralInfo($car->id, $data);
            $this->carRepository->createFinancialInfo($car->id, $data);
            $this->carRepository->createTechnicalSpec($car->id, $data);

            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $path = $image->store('cars', 'public');
                        $path = 'storage/' . $path;

                        $isMain = (isset($data['main_image_index']) && $data['main_image_index'] == $index);
                        $this->carRepository->createImage($car->id, $path, $isMain);
                    }
                }
            }


            DB::commit();

            $car->load(['generalInfo', 'financialInfo', 'technicalSpecs', 'images']);

            return [
                'status' => true,
                'message' => 'Car added successfully',
                'car' => $car,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'message' => 'Failed to add car: ' . $e->getMessage(),
            ];
        }
    }
    public function deleteCar(int $car_id, int $showroom_id): array
    {
        $userID = auth()->id();

        $deleted = $this->carRepository->delete($userID, $showroom_id, $car_id);

        if ($deleted) {
            return [
                'status' => true,
                'message' => 'Car deleted successfully',
            ];
        }

        return [
            'status' => false,
            'message' => 'Car not found or you do not have permission',
        ];
    }
    public function getCarById(int $showroom_id, int $car_id): ?object
    {
        $car = $this->carRepository->findCar($showroom_id, $car_id);

        return $car;
    }
    public function listCars(int $showroom_id): ?\Illuminate\Support\Collection
    {
        $userID = auth()->id();

        $showRoom = Showroom::where('id', $showroom_id)
            ->where('user_id', $userID)
            ->first();

        if (!$showRoom) {
            return null;
        }

        return $this->carRepository->listCarsInShowroom($showroom_id);
    }
    public function updateCar(int $carID, array $data): array
    {
        $userID = auth()->id();

        $car = Car::where('id', $carID)
            ->where('user_id', $userID)
            ->with(['generalInfo', 'financialInfo', 'technicalSpecs', 'images'])
            ->first();

        if (!$car) {
            return [
                'status' => false,
                'message' => 'Car not found or unauthorized.'
            ];
        }

        DB::beginTransaction();

        try {
            // تحديث المعلومات العامة
            $generalInfoFields = [
                'name',
                'brand',
                'model',
                'gear_box',
                'year',
                'fuel_type',
                'body_type',
                'vin',
                'condition'
            ];

            $generalInfoData = [];
            foreach ($generalInfoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $generalInfoData[$field] = $data[$field];
                }
            }

            if (!empty($generalInfoData) && $car->generalInfo) {
                $car->generalInfo->update($generalInfoData);
            }

            // تحديث المعلومات المالية
            $financialInfoFields = [
                'price',
                'currency',
                'negotiable',
                'discount_percentage',
                'discount_amount'
            ];

            $financialInfoData = [];
            foreach ($financialInfoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $financialInfoData[$field] = $data[$field];
                }
            }

            if (!empty($financialInfoData) && $car->financialInfo) {
                $car->financialInfo->update($financialInfoData);
            }

            // تحديث المواصفات التقنية
            $technicalSpecFields = ['horse_power', 'engine_type', 'cylinders'];

            $technicalSpecData = [];
            foreach ($technicalSpecFields as $field) {
                if (array_key_exists($field, $data)) {
                    $technicalSpecData[$field] = $data[$field];
                }
            }

            if (!empty($technicalSpecData) && $car->technicalSpecs) {
                $car->technicalSpecs->update($technicalSpecData);
            }

            // تحديث حقول السيارة الأساسية
            $carMainFields = ['is_rentable', 'rental_cost_per_hour'];

            $carMainData = [];
            foreach ($carMainFields as $field) {
                if (array_key_exists($field, $data)) {
                    $carMainData[$field] = $data[$field];
                }
            }

            if (!empty($carMainData)) {
                $car->update($carMainData);
            }


            if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {

                foreach ($car->images as $image) {

                    $fullPath = public_path($image->image_path);
                    if (File::exists($fullPath)) {
                        File::delete($fullPath);
                    }

                    $image->delete();
                }

                $mainImageIndex = $data['main_image_index'] ?? 0;

                foreach ($data['images'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $path = $image->store('cars', 'public');
                        $relativePath = 'storage/' . $path;

                        $isMain = ($mainImageIndex == $index);
                        $this->carRepository->createImage($car->id, $relativePath, $isMain);
                    }
                }
            }

            DB::commit();

            $car->refresh();
            $car->load(['generalInfo', 'financialInfo', 'technicalSpecs', 'images']);

            return [
                'status' => true,
                'message' => 'Car updated successfully.',
                'car' => $car,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to update car: ' . $e->getMessage(),
            ];
        }
    }
    public function changeStatus(int $carID, string $status): array
    {
        $car = $this->carRepository->findCarById($carID);

        if (!$car) {
            return [
                'status' => false,
                'message' => 'Car not found.'
            ];
        }

        $userID = auth()->id();

        if ($car->user_id !== $userID) {
            return [
                'status' => false,
                'message' => 'Unauthorized.'
            ];
        }

        $allowedStatuses = ['available', 'sold', 'reserved'];

        if (!in_array($status, $allowedStatuses)) {
            return [
                'status' => false,
                'message' => 'Invalid status.'
            ];
        }

        $updated = $this->carRepository->updateStatus($carID, $status);

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
        return $this->carRepository->getRandomCarsFromMultipleShowrooms($limit);
    }
}
