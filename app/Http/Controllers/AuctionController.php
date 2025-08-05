<?php


namespace App\Http\Controllers;

use App\Services\AuctionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuctionController extends Controller
{
    protected AuctionService $service;

    public function __construct(AuctionService $service)
    {
        $this->service = $service;
    }

    public function createAuction(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'showroom_id' => 'required|exists:showrooms,id',
            'starting_price' => 'required|numeric|min:0',
            'minimum_increment' => 'required|numeric|min:1',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'extend_on_last_minute' => 'boolean',
        ]);

        $auction = $this->service->createAuction($request->all());

        return response()->json(['status' => 'success', 'auction' => $auction], 201);
    }

    public function updateAuction(Request $request, $id)
    {
        $auction = $this->service->updateAuction($id, $request->all());

        return response()->json(['status' => $auction ? 'success' : 'error']);
    }

    public function deleteAuction($id)
    {
        $deleted = $this->service->deleteAuction($id);

        return response()->json(['status' => $deleted ? 'success' : 'error']);
    }

    public function cancelAuction($id)
    {
        $canceled = $this->service->cancelAuction($id);

        return response()->json(['status' => $canceled ? 'success' : 'error']);
    }

    public function getShowroomAuctions($showroomId)
    {
        return response()->json($this->service->getShowroomAuctions($showroomId));
    }
    public function placeBid(Request $request)
    {
        $request->validate([
            'auction_id' => 'required|exists:auctions,id',
            'bid_amount' => 'required|numeric|min:1'
        ]);

        $result = $this->service->placeBid(
            $request->auction_id,
            auth()->id(),
            $request->bid_amount
        );

        return response()->json($result, $result['status'] === 'success' ? 200 : 400);
    }

    public function closeAuction($id)
    {
        $result = $this->service->closeAuction($id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 400);
    }

    public function getAuction($id)
    {
        return response()->json($this->service->getAuctionDetails($id));
    }

    public function getActiveAuctions()
    {
        return response()->json($this->service->getActiveAuctions());
    }

    public function getBids($auctionId)
    {
        return response()->json($this->service->getBids($auctionId));
    }
}
