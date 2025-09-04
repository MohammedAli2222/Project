<?php
// app/Services/AuctionService.php

namespace App\Services;

use App\Models\History;
use App\Repositories\AuctionRepository;
use Carbon\Carbon;

class AuctionService
{
    protected AuctionRepository $repo;

    public function __construct(AuctionRepository $repo)
    {
        $this->repo = $repo;
    }


    public function createAuction(array $data)
    {
        return $this->repo->create($data);
    }

    public function updateAuction(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteAuction(int $id)
    {
        return $this->repo->delete($id);
    }

    public function getShowroomAuctions(int $showroomId)
    {
        return $this->repo->getAllForShowroom($showroomId);
    }

    public function cancelAuction(int $id)
    {
        return $this->repo->cancel($id);
    }


    public function placeBid(int $auctionId, int $userId, float $amount): array
    {
        $auction = $this->repo->findById($auctionId);
        if (!$auction) {
            return ['status' => 'error', 'message' => 'Auction not found.'];
        }

        $now = now();
        $end = $auction->extended_until ?? $auction->end_time;

        if ($auction->status !== 'active' || $now->lt($auction->start_time) || $now->gt($end)) {
            return ['status' => 'error', 'message' => 'This auction is not currently active.'];
        }

        if ($amount < $auction->current_price + $auction->minimum_increment) {
            return ['status' => 'error', 'message' => 'Your bid is too low.'];
        }

        // إضافة المزايدة
        $bid = $this->repo->addBid($auctionId, $userId, $amount);
        $this->repo->updateCurrentPrice($auctionId, $amount);

        // تمديد المزاد إذا كان الفرق أقل من دقيقة
        if ($auction->extend_on_last_minute && $now->diffInSeconds($end) <= 60) {
            $newEnd = Carbon::parse($end)->addMinutes(5);
            $this->repo->extendAuction($auctionId, $newEnd->toDateTimeString());
        }

        History::create([
            'user_id' => $userId,
            'car_id' => $auction->car_id,
            'action' => "Placed a bid of {$amount} on auction #{$auctionId}",
        ]);

        return [
            'status' => 'success',
            'message' => 'Bid placed successfully.',
            'bid' => $bid
        ];
    }


    public function closeAuction(int $auctionId): array
    {
        $auction = $this->repo->findById($auctionId);
        if (!$auction) return ['status' => 'error', 'message' => 'Auction not found.'];

        if ($auction->status !== 'active') {
            return ['status' => 'error', 'message' => 'Auction is already closed or not active.'];
        }

        $highestBid = $this->repo->getHighestBid($auctionId);
        if ($highestBid) {
            $this->repo->updateWinner($auctionId, $highestBid->user_id);
        }

        $this->repo->updateStatus($auctionId, 'ended');

        return ['status' => 'success', 'message' => 'Auction closed.', 'winner_id' => $highestBid?->user_id];
    }

    public function getAuctionDetails(int $id)
    {
        return $this->repo->findById($id);
    }

    public function getActiveAuctions()
    {
        return $this->repo->getActive();
    }

    public function getBids(int $auctionId)
    {
        return $this->repo->getBidsForAuction($auctionId);
    }
}
