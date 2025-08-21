<?php

namespace App\Http\Controllers;

use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RentalController extends Controller
{
    protected RentalService $service;

    public function __construct(RentalService $service)
    {
        $this->service = $service;
    }

    public function markCarAsRentable(Request $request)
    {
        $request->validate([
            'car_id' => 'required',
            'rental_cost_per_hour' => 'required|numeric|min:1'
        ]);

        $result = $this->service->markCarAsRentable(
            $request->car_id,
            $request->rental_cost_per_hour
        );

        return response()->json($result, $result['status'] === 'success' ? 200 : 400);
    }

    public function createRental(Request $request)
    {
        $request->validate([
            'car_id' => 'required',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s'
        ]);

        $result = $this->service->createRental(
            auth()->id(),
            $request->car_id,
            $request->start_date,
            $request->end_date
        );

        return response()->json($result, $result['status'] === 'success' ? 201 : 400);
    }

    public function confirmRental(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:completed,cancelled,active,confirmed'
        ]);

        $result = $this->service->confirmRental($id, $request->status);

        return response()->json($result, $result['status'] === 'success' ? 200 : 400);
    }

    public function getUserRentals()
    {
        $result = $this->service->getUserRentals(auth()->id());
        return response()->json($result);
    }

    public function getShowroomRentals($showroomId)
    {
        $result = $this->service->getShowroomRentals($showroomId);
        return response()->json($result);
    }

    public function getRentalDetails($rentalId)
    {
        $result = $this->service->getRentalDetails($rentalId);
        return response()->json($result);
    }
}
