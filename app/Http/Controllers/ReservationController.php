<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReservationService;
use Illuminate\Routing\Controller;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|integer|exists:cars,id',
            'showroom_id' => 'required|integer|exists:showrooms,id',
            'deposit_amount' => 'required|numeric|min:1',
        ]);

        $result = $this->reservationService->reserveCar(
            $validated['car_id'],
            $request->user()->id,
            $validated['showroom_id'],
            $validated['deposit_amount']
        );

        return response()->json($result);
    }

    public function confirm($id)
    {
        $result = $this->reservationService->updateReservationStatus($id, 'confirmed');
        return response()->json($result);
    }

    public function cancel($id)
    {
        $result = $this->reservationService->updateReservationStatus($id, 'cancelled');
        return response()->json($result);
    }

    public function complete($id)
    {
        $result = $this->reservationService->completePurchase($id);
        return response()->json($result);
    }

    public function index()
    {
        $reservations = $this->reservationService->getAllReservations();
        return response()->json([
            'status' => true,
            'data' => $reservations
        ]);
    }
}
