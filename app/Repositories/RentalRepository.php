<?php

namespace App\Repositories;

use App\Models\Rental;
use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RentalRepository
{
    public function create(array $data): Rental
    {
        return Rental::create($data);
    }

    public function markCarAsRented(int $carId): void
    {
        Car::where('id', $carId)->update(['available_status' => 'available']);
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

    public function checkOverlapAndSuggest(
        int $carId,
        string $requestedStart,
        string $requestedEnd,
        ?int $excludeUserId = null
    ): ?array {
        $overlaps = Rental::where('car_id', $carId)
            ->whereIn('status', ['active', 'confirmed'])
            ->when($excludeUserId, function ($q) use ($excludeUserId) {
                $q->where('user_id', '!=', $excludeUserId);
            })
            ->where(function ($q) use ($requestedStart, $requestedEnd) {
                $q->where(function ($qq) use ($requestedStart, $requestedEnd) {
                    $qq->where('start_date', '<', $requestedEnd)
                        ->where('end_date', '>', $requestedStart);
                });
            })
            ->exists();

        if (!$overlaps) {
            return null;
        }


        $searchStart = Carbon::parse($requestedStart)->subDay()->toDateTimeString();
        $searchEnd = Carbon::parse($requestedEnd)->addDays(2)->toDateTimeString();

        $bookings = Rental::where('car_id', $carId)
            ->whereIn('status', ['active', 'confirmed'])
            ->when($excludeUserId, function ($q) use ($excludeUserId) {
                $q->where('user_id', '!=', $excludeUserId);
            })
            ->where('end_date', '>', $searchStart)
            ->where('start_date', '<', $searchEnd)
            ->orderBy('start_date', 'asc')
            ->get();

        $availablePeriods = [];
        $currentTime = Carbon::now();

        if ($bookings->isEmpty()) {
            $availablePeriods[] = [
                'start' => $requestedStart,
                'end' => Carbon::parse($requestedStart)->addHours(2)->toDateTimeString()
            ];
            return $availablePeriods;
        }

        $firstBooking = $bookings->first();
        if ($currentTime < $firstBooking->start_date) {
            $availablePeriods[] = [
                'start' => $currentTime->toDateTimeString(),
                'end' => $firstBooking->start_date
            ];
        }

        for ($i = 0; $i < count($bookings) - 1; $i++) {
            $currentBooking = $bookings[$i];
            $nextBooking = $bookings[$i + 1];

            $gapStart = Carbon::parse($currentBooking->end_date);
            $gapEnd = Carbon::parse($nextBooking->start_date);

            if ($gapStart->diffInHours($gapEnd) >= 1) {
                $availablePeriods[] = [
                    'start' => $gapStart->toDateTimeString(),
                    'end' => $gapEnd->toDateTimeString()
                ];
            }
        }

        $lastBooking = $bookings->last();
        $afterLast = Carbon::parse($lastBooking->end_date);
        $availablePeriods[] = [
            'start' => $afterLast->toDateTimeString(),
            'end' => $afterLast->addDays(1)->toDateTimeString() 
        ];

        return $availablePeriods;
    }
}
