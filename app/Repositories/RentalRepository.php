<?php

namespace App\Repositories;

use App\Models\Rental;
use App\Models\Car;
use Illuminate\Support\Collection;

class RentalRepository
{
    public function create(array $data): Rental
    {
        return Rental::create($data);
    }

    public function markCarAsRented(int $carId): void
    {
        Car::where('id', $carId)->update(['available_status' => 'rented']);
    }

    public function markCarAsAvailable(int $carId): void
    {
        Car::where('id', $carId)->update(['available_status' => 'available']);
    }

    public function markCarAsRentable(int $carId, float $price): void
    {
        Car::where('id', $carId)->update([
            'is_rentable' => true,
            'rental_cost_per_hour' => $price,
            'available_status' => 'available'
        ]);
    }

    public function findById(int $id): ?Rental
    {
        return Rental::with(['car', 'user', 'showroom'])->find($id);
    }

    public function getAllForUser(int $userId): Collection
    {
        return Rental::where('user_id', $userId)->with('car')->get();
    }

    public function getAllForShowroom(int $showroomId): Collection
    {
        return Rental::where('showroom_id', $showroomId)->with('car', 'user')->get();
    }

    public function updateStatus(int $id, string $status): Rental
    {
        $rental = Rental::findOrFail($id);
        $rental->status = $status;
        $rental->save();
        return $rental;
    }

    public function hasOverlap(int $carId, string $start, string $end): bool
    {
        return Rental::where('car_id', $carId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                    });
            })
            ->exists();
    }
}
