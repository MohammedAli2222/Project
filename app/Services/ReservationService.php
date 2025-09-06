<?php

namespace App\Services;

use App\Repositories\ReservationRepository;
use App\Models\Car;
use App\Models\PersonalCar;
use Exception;

class ReservationService
{
    protected $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }
    public function reserveCar(int $carId, int $userId, ?int $showroomId, float $deposit): array
    {
        try {
            // تحديد نوع السيارة تلقائي
            $carType = $showroomId ? Car::class : PersonalCar::class;

            // جلب السيارة حسب النوع
            $car = $carType::find($carId);
            if (!$car) {
                return ['status' => false, 'message' => 'Car not found'];
            }

            // تحقق من حالة السيارة
            if ($car->available_status !== 'available') {
                return ['status' => false, 'message' => 'Car is not available for reservation'];
            }

            // تحقق من حجز مسبق
            $existing = $this->reservationRepository->findActiveByCarAndUser($carId, $userId, $carType);
            if ($existing) {
                return ['status' => false, 'message' => 'You already reserved this car'];
            }

            // تحقق من مبلغ العربون (>= 10% من السعر)
            if ($deposit < ($car->price * 0.1)) {
                return ['status' => false, 'message' => 'Deposit must be at least 10% of car price'];
            }

            // إنشاء الحجز
            $reservation = $this->reservationRepository->create([
                'car_id' => $carId,
                'carable_type' => $carType,
                'user_id' => $userId,
                'showroom_id' => $showroomId,
                'reservation_date' => now(),
                'deposit_amount' => $deposit,
                'status' => 'pending',
                'expires_at' => now()->addHours(12),
            ]);

            // تحديث حالة السيارة
            $car->update(['available_status' => 'reserved']);

            return ['status' => true, 'data' => $reservation];
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }



    public function completePurchase(int $reservationId): array
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if (!$reservation) {
            return ['status' => false, 'message' => 'Reservation not found'];
        }

        // تأكد من الحالة
        if ($reservation->status !== 'confirmed') {
            return ['status' => false, 'message' => 'Reservation must be confirmed before completing purchase'];
        }

        // تأكد من حالة السيارة
        $car = $reservation->car;
        if (in_array($car->available_status, ['sold', 'rented'])) {
            return ['status' => false, 'message' => 'Car is no longer available for purchase'];
        }

        // تحديث الحجز والسيارة
        $this->reservationRepository->updateStatus($reservationId, 'completed');
        $car->update(['available_status' => 'sold']);

        return ['status' => true, 'message' => 'Purchase completed successfully'];
    }

    public function updateReservationStatus(int $reservationId, string $status): array
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if (!$reservation) {
            return ['status' => false, 'message' => 'Reservation not found'];
        }

        // ممنوع تعديل الحجز إذا منتهي
        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            return ['status' => false, 'message' => 'Cannot update a completed or cancelled reservation'];
        }

        $this->reservationRepository->updateStatus($reservationId, $status);

        // تحديث حالة السيارة
        if ($status === 'cancelled') {
            $reservation->car->update(['available_status' => 'available']);
        } elseif ($status === 'confirmed') {
            $reservation->car->update(['available_status' => 'reserved']);
        }

        return ['status' => true, 'message' => 'Reservation updated'];
    }
    public function expireReservations(): array
    {
        $expired = $this->reservationRepository->findExpiredReservations();

        foreach ($expired as $reservation) {
            if ($reservation->status === 'pending') {
                $this->reservationRepository->updateStatus($reservation->id, 'cancelled');
                $reservation->car->update(['available_status' => 'available']);
            }
        }

        return ['status' => true, 'message' => 'Expired reservations cleaned up'];
    }
    public function getAllReservations()
    {
        return $this->reservationRepository->all();
    }
}
