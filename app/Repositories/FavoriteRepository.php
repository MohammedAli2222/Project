<?php

namespace App\Repositories;

use App\Models\car_favorite;

class FavoriteRepository
{

     public function add($userId, $carId)
    {
        return car_favorite::firstOrCreate([
            'user_id' => $userId,
            'car_id' => $carId
        ]);
    }

    public function remove($userId, $carId)
    {
        return car_favorite::where('user_id', $userId)
            ->where('car_id', $carId)
            ->delete();
    }

    public function exists($userId, $carId)
    {
        return car_favorite::where('user_id', $userId)
            ->where('car_id', $carId)
            ->exists();
    }

    public function getAllForUser($userId)
    {
        return car_favorite::with('car')
            ->where('user_id', $userId)
            ->get();
    }
}
