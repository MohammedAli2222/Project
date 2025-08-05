<?php

namespace App\Services;

use App\Repositories\FavoriteRepository;
use App\Models\Car;

class FavoriteService
{
    protected FavoriteRepository $favoriteRepository;

    public function __construct(FavoriteRepository $favoriteRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
    }


    public function addFavorite($userId, $carId)
    {
        if (!Car::find($carId)) {
            return [
                'status' => 'error',
                'message' => 'The selected car does not exist.'
            ];
        }

        if (!$this->favoriteRepository->exists($userId, $carId)) {
            $this->favoriteRepository->add($userId, $carId);
            return [
                'status' => 'success',
                'message' => 'Car has been added to favorites.'
            ];
        }

        return [
            'status' => 'info',
            'message' => 'Car is already in your favorites.'
        ];
    }

    public function removeFavorite($userId, $carId)
    {
        if ($this->favoriteRepository->exists($userId, $carId)) {
            $this->favoriteRepository->remove($userId, $carId);
            return [
                'status' => 'success',
                'message' => 'Car has been removed from favorites.'
            ];
        }

        return [
            'status' => 'warning',
            'message' => 'Car was not found in your favorites.'
        ];
    }

    public function listFavorites($userId)
    {
        return $this->favoriteRepository->getAllForUser($userId);
    }
}
