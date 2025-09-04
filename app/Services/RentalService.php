<?php

namespace App\Services;

use App\Repositories\RentalRepository;
use App\Models\Car;
use App\Models\rental;
use Carbon\Carbon;

class RentalService
{
    protected RentalRepository $rentalRepository;

    public function __construct(RentalRepository $rentalRepository)
    {
        $this->rentalRepository = $rentalRepository;
    }

    public function markCarAsRentable(int $carId, float $price): array
    {
        $officer = auth()->user()->id;

        $car = Car::where('id', $carId)->where('user_id', $officer)->first();
        if ($car) {
            $isRentable = $car->is_rentable == true;
            if ($isRentable) {
                return [
                    'status' => 'error',
                    'message' => 'Car is rentable.'
                ];
            }
        }
        if (!$car) {
            return [
                'status' => 'error',
                'message' => 'Car not found or dont have this car.'
            ];
        }

        $this->rentalRepository->markCarAsRentable($carId, $price);

        return ['status' => 'success', 'message' => 'Car marked as rentable.'];
    }

    public function createRental($userId, $carId, $startDate, $endDate): array
    {
        $car = Car::find($carId);

        if (!$car || !$car->is_rentable || $car->available_status !== 'available') {
            return ['status' => 'error', 'message' => 'This car is not available for rent.'];
        }

        // التحقق من عدم وجود حجز نشط لنفس المستخدم والسيارة
        $existingRental = Rental::where('user_id', $userId)
            ->where('car_id', $carId)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->first();

        if ($existingRental) {
            return ['status' => 'error', 'message' => 'You already have an active rental for this car.'];
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lessThanOrEqualTo($start)) {
            return ['status' => 'error', 'message' => 'Invalid rental duration.'];
        }

        // التحقق من التداخل مع الحجوزات الأخرى
        $suggestions = $this->rentalRepository->checkOverlapAndSuggest(
            $carId,
            $startDate,
            $endDate,
            auth()->id()
        );

        if ($suggestions !== null) {
            return [
                'status' => 'error',
                'message' => 'Car is already rented during this period.',
                'suggestions' => $suggestions
            ];
        }

        $hours = $start->diffInHours($end);
        $totalCost = $car->rental_cost_per_hour * $hours;

        $rental = $this->rentalRepository->create([
            'user_id' => $userId,
            'car_id' => $carId,
            'showroom_id' => $car->showroom_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'rental_cost_per_hour' => $car->rental_cost_per_hour,
            'total_cost' => $totalCost,
            'status' => Rental::STATUS_PENDING
        ]);

        $this->rentalRepository->markCarAsRented($carId);

        // ✅ تسجيل العملية في history
        \App\Models\History::create([
            'user_id' => $userId,
            'car_id' => $carId,
            'action' => 'Rent',
        ]);

        return [
            'status' => 'success',
            'message' => 'Rental created successfully.',
            'rental' => $rental
        ];
    }


    public function confirmRental($rentalId, $status): array
    {
        if (!in_array($status, ['completed', 'cancelled', 'active', 'confirmed'])) {
            return ['status' => 'error', 'message' => 'Invalid status.'];
        }

        $updated = $this->rentalRepository->updateStatus($rentalId, $status);

        if ($status === 'cancelled') {
            $this->rentalRepository->markCarAsAvailable($updated->car_id);
        }

        return ['status' => 'success', 'message' => 'Rental status updated.', 'rental' => $updated];
    }

    public function getUserRentals($userId)
    {
        return $this->rentalRepository->getAllForUser($userId);
    }

    public function getShowroomRentals($showroomId)
    {
        return $this->rentalRepository->getAllForShowroom($showroomId);
    }

    public function getRentalDetails($rentalId)
    {
        return $this->rentalRepository->findById($rentalId);
    }
}
