<?php

namespace App\Repositories;

use App\Models\Reservation;

class ReservationRepository
{
    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function findById(int $id): ?Reservation
    {
        return Reservation::with('car')->find($id);
    }

    public function findActiveByCarAndUser(int $carId, int $userId): ?Reservation
    {
        return Reservation::where('car_id', $carId)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();
    }

    public function updateStatus(int $reservationId, string $status): bool
    {
        return Reservation::where('id', $reservationId)->update(['status' => $status]);
    }

    public function findExpiredReservations()
    {
        return Reservation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();
    }

    public function all()
    {
        return Reservation::with('car', 'user', 'showroom')->get();
    }
}
