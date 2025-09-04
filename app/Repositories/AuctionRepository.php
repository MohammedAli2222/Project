<?php


namespace App\Repositories;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Support\Collection;

class AuctionRepository
{

    public function update(int $auctionId, array $data): bool
    {
        return Auction::where('id', $auctionId)->update($data);
    }

    public function delete(int $auctionId): bool
    {
        return Auction::where('id', $auctionId)->delete();
    }

    public function getAllForShowroom(int $showroomId): Collection
    {
        return Auction::where('showroom_id', $showroomId)->with('car')->get();
    }

    public function cancel(int $auctionId): bool
    {
        return Auction::where('id', $auctionId)->update(['status' => 'cancelled']);
    }

    public function create(array $data): Auction
    {
        return Auction::create($data);
    }

    public function findById(int $id): ?Auction
    {
        return Auction::with('car', 'bids')->find($id);
    }

    public function getActive(): Collection
    {
        return Auction::where('status', 'active')->with('car')->get();
    }

    public function updateStatus(int $auctionId, string $status): void
    {
        Auction::where('id', $auctionId)->update(['status' => $status]);
    }

    public function updateCurrentPrice(int $auctionId, float $newPrice): void
    {
        Auction::where('id', $auctionId)->update(['current_price' => $newPrice]);
    }

    public function updateWinner(int $auctionId, int $userId): void
    {
        Auction::where('id', $auctionId)->update(['winner_id' => $userId]);
    }

    public function addBid(int $auctionId, int $userId, float $amount): Bid
    {
        return Bid::create([
            'auction_id' => $auctionId,
            'user_id' => $userId,
            'bid_amount' => $amount,
            'bid_time' => now()
        ]);
    }

    public function getHighestBid(int $auctionId): ?Bid
    {
        return Bid::where('auction_id', $auctionId)->orderByDesc('bid_amount')->first();
    }

    public function getBidsForAuction(int $auctionId): Collection
    {
        return Bid::where('auction_id', $auctionId)->orderByDesc('bid_amount')->get();
    }

    public function extendAuction(int $auctionId, string $newEndTime): void
    {
        Auction::where('id', $auctionId)->update(['extended_until' => $newEndTime]);
    }
}
