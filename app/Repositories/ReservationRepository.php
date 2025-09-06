<?php

namespace App\Repositories;

use App\Models\Reservation;

class ReservationRepository
{
    // إنشاء حجز
    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    // جلب الحجز حسب ID
    public function findById(int $id): ?Reservation
    {
        return Reservation::with('carable', 'user', 'showroom')->find($id);
    }

    // جلب حجز فعال للمستخدم ونوع سيارة معين
    public function findActiveByCarAndUser(int $carId, int $userId, string $carType): ?Reservation
    {
        return Reservation::where('car_id', $carId)
            ->where('carable_type', $carType)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();
    }

    // تحديث حالة الحجز
    public function updateStatus(int $reservationId, string $status): bool
    {
        return Reservation::where('id', $reservationId)->update(['status' => $status]);
    }

    // الحجزات المنتهية
    public function findExpiredReservations()
    {
        return Reservation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();
    }

    // جلب كل الحجزات مع معلومات السيارة، المستخدم والمعرض
    public function all()
    {
        return Reservation::with('carable', 'user', 'showroom')->get();
    }
}
