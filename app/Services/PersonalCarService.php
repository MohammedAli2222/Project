<?php

namespace App\Services;

use App\Models\PersonalCar;
use App\Repositories\PersonalCarRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PersonalCarService
{
    public function __construct(
        private PersonalCarRepository $repo
    ) {}

    public function create(array $data, int $userId): PersonalCar
    {
        return DB::transaction(function() use ($data, $userId) {

            // 1) personal_cars
            $car = $this->repo->createCar([
                'user_id'             => $userId,
                'condition'           => $data['condition'],
                'vin'                 => $data['vin'],
                'available_status'    => $data['available_status'] ?? 'available',
                'is_rentable'         => (bool)($data['is_rentable'] ?? false),
                'rental_cost_per_hour'=> $data['rental_cost_per_hour'] ?? null,
            ]);

            // 2) personal_car_infos
            $this->repo->createInfo($car->id, [
                'name'        => $data['name'],
                'brand'       => $data['brand'],
                'model'       => $data['model'],
                'gear_box'    => $data['gear_box'],
                'year'        => $data['year'],
                'fuel_type'   => $data['fuel_type'],
                'body_type'   => $data['body_type'],
                'color'       => $data['color'],
                'engine_type' => $data['engine_type'],
                'cylinders'   => $data['cylinders'],
                'horse_power' => $data['horse_power'],
                'price'       => $data['price'],
                'currency'    => $data['currency'],
                'negotiable'  => (bool)($data['negotiable'] ?? false),
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_amount'     => $data['discount_amount'] ?? null,
            ]);

            // 3) images (اختياري)
            $pathsAndMain = $this->storeImages($data['images'] ?? [], $data['main_image_index'] ?? null);
            if (!empty($pathsAndMain)) {
                $this->repo->addImages($car->id, $pathsAndMain);
            }

            return $this->repo->getById($car->id);
        });
    }

    public function update(int $id, array $data, int $userId): PersonalCar
    {
        return DB::transaction(function() use ($id, $data, $userId) {
            $car = $this->repo->getById($id);
            abort_unless($car, 404, 'Personal car not found');
            abort_unless($car->user_id === $userId, 403, 'Unauthorized');

            // car
            $this->repo->updateCar($car, array_filter([
                'condition'           => $data['condition'] ?? null,
                'available_status'    => $data['available_status'] ?? null,
                'is_rentable'         => array_key_exists('is_rentable', $data) ? (bool)$data['is_rentable'] : null,
                'rental_cost_per_hour'=> $data['rental_cost_per_hour'] ?? null,
            ], fn($v) => !is_null($v)));

            // info
            $this->repo->updateInfo($car, array_filter([
                'name'        => $data['name'] ?? null,
                'brand'       => $data['brand'] ?? null,
                'model'       => $data['model'] ?? null,
                'gear_box'    => $data['gear_box'] ?? null,
                'year'        => $data['year'] ?? null,
                'fuel_type'   => $data['fuel_type'] ?? null,
                'body_type'   => $data['body_type'] ?? null,
                'color'       => $data['color'] ?? null,
                'engine_type' => $data['engine_type'] ?? null,
                'cylinders'   => $data['cylinders'] ?? null,
                'horse_power' => $data['horse_power'] ?? null,
                'price'       => $data['price'] ?? null,
                'currency'    => $data['currency'] ?? null,
                'negotiable'  => array_key_exists('negotiable', $data) ? (bool)$data['negotiable'] : null,
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_amount'     => $data['discount_amount'] ?? null,
            ], fn($v) => !is_null($v)));

            // images (اختياري: استبدال كامل)
            if (!empty($data['images'])) {
                $pathsAndMain = $this->storeImages($data['images'], $data['main_image_index'] ?? null);
                $this->repo->replaceImages($car, $pathsAndMain);
            }

            return $this->repo->getById($car->id);
        });
    }

    public function list(array $filters = [], int $perPage = 15) {
        return $this->repo->paginate($filters, $perPage);
    }

    public function delete(int $id, int $userId): void {
        DB::transaction(function() use ($id, $userId) {
            $car = $this->repo->getById($id);
            abort_unless($car, 404, 'Personal car not found');
            abort_unless($car->user_id === $userId, 403, 'Unauthorized');

            // حذف ملفات الصور من التخزين أيضًا (اختياري)
            foreach ($car->images as $img) {
                if (Storage::disk('public')->exists($img->image_path)) {
                    Storage::disk('public')->delete($img->image_path);
                }
            }

            $this->repo->delete($car);
        });
    }

    /**
     * @param UploadedFile[]|array $images
     * @param int|null $mainIndex   فهرس الصورة الرئيسية (0-based)
     * @return array [['path' => 'personal_cars/xxx.jpg','is_main' => bool], ...]
     */
    private function storeImages(array $images, ?int $mainIndex): array {
        $result = [];
        foreach ($images as $i => $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('personal_cars', 'public');
            } else {
                // لو مررت مسار جاهز كسلسلة
                $path = (string)$file;
            }
            $result[] = [
                'path'    => $path,
                'is_main' => $mainIndex !== null && $i === (int)$mainIndex,
            ];
        }
        return $result;
    }
}
