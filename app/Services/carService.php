<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Showroom;
use App\Repositories\CarRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
                $imagePathBase = 'car_images/';

                foreach ($data['images'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $destinationPath = public_path($imagePathBase);

                        if (!File::isDirectory($destinationPath)) {
                            File::makeDirectory($destinationPath, 0755, true, true);
                        }

                        $image->move($destinationPath, $imageName);
                        $relativePath = $imagePathBase . $imageName;

                        $isMain = (isset($data['main_image_index']) && $data['main_image_index'] == $index);
                        $this->carRepository->createImage($car->id, $relativePath, $isMain);
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
        $car = $this->carRepository->findCarById($carID);

        if (!$car) {
            return [
                'status' => false,
                'message' => 'Car not found.'
            ];
        }

        DB::beginTransaction();

        try {
            if (isset($data['name']) || isset($data['brand']) || isset($data['model']) || isset($data['gear_box']) || isset($data['year']) || isset($data['fuel_type']) || isset($data['body_type']) || isset($data['vin']) || isset($data['condition'])) {
                $car->generalInfo->update([
                    'name' => $data['name'] ?? $car->generalInfo->name,
                    'brand' => $data['brand'] ?? $car->generalInfo->brand,
                    'model' => $data['model'] ?? $car->generalInfo->model,
                    'gear_box' => $data['gear_box'] ?? $car->generalInfo->gear_box,
                    'year' => $data['year'] ?? $car->generalInfo->year,
                    'fuel_type' => $data['fuel_type'] ?? $car->generalInfo->fuel_type,
                    'body_type' => $data['body_type'] ?? $car->generalInfo->body_type,
                    'vin' => $data['vin'] ?? $car->generalInfo->vin,
                    'condition' => $data['condition'] ?? $car->generalInfo->condition,
                ]);
            }

            if (isset($data['price']) || isset($data['currency']) || isset($data['negotiable']) || isset($data['discount_percentage']) || isset($data['discount_amount'])) {
                $car->financialInfo->update([
                    'price' => $data['price'] ?? $car->financialInfo->price,
                    'currency' => $data['currency'] ?? $car->financialInfo->currency,
                    'negotiable' => $data['negotiable'] ?? $car->financialInfo->negotiable,
                    'discount_percentage' => $data['discount_percentage'] ?? $car->financialInfo->discount_percentage,
                    'discount_amount' => $data['discount_amount'] ?? $car->financialInfo->discount_amount,
                ]);
            }

            if (isset($data['horse_power']) || isset($data['engine_type']) || isset($data['cylinders'])) {
                $car->technicalSpecs->update([
                    'horse_power' => $data['horse_power'] ?? $car->technicalSpecs->horse_power,
                    'engine_type' => $data['engine_type'] ?? $car->technicalSpecs->engine_type,
                    'cylinders' => $data['cylinders'] ?? $car->technicalSpecs->cylinders,
                ]);
            }

            if (isset($data['images']) && is_array($data['images'])) {
                $imagePathBase = 'car_images/';

                foreach ($car->images as $image) {
                    if (File::exists(public_path($image->image_path))) {
                        File::delete(public_path($image->image_path));
                    }
                    $image->delete();
                }


                foreach ($data['images'] as $index => $image) {
                    if ($image instanceof UploadedFile) {
                        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                        $destinationPath = public_path($imagePathBase);

                        if (!File::isDirectory($destinationPath)) {
                            File::makeDirectory($destinationPath, 0755, true, true);
                        }

                        $image->move($destinationPath, $imageName);
                        $relativePath = $imagePathBase . $imageName;

                        $isMain = (isset($data['main_image_index']) && $data['main_image_index'] == $index);
                        $this->carRepository->createImage($car->id, $relativePath, $isMain);
                    }
                }
            }

            DB::commit();

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
