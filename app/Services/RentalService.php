<?php

namespace App\Services;

use App\Repositories\RentalRepository;
use App\Models\Car;
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
        $car = Car::find($carId);
        if (!$car) {
            return ['status' => 'error', 'message' => 'Car not found.'];
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

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lessThanOrEqualTo($start)) {
            return ['status' => 'error', 'message' => 'Invalid rental duration.'];
        }

        if ($this->rentalRepository->hasOverlap($carId, $startDate, $endDate)) {
            return ['status' => 'error', 'message' => 'Car is already rented during this period.'];
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
            'status' => 'active'
        ]);

        $this->rentalRepository->markCarAsRented($carId);

        return ['status' => 'success', 'message' => 'Rental created and car marked as rented.', 'rental' => $rental];
    }

    public function confirmRental($rentalId, $status): array
    {
        if (!in_array($status, ['completed', 'cancelled'])) {
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
