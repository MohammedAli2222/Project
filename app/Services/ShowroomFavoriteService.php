<?php
    
namespace App\Services;

use App\Repositories\ShowroomFavoriteRepository;
use App\Models\Showroom;

class ShowroomFavoriteService
{
    protected ShowroomFavoriteRepository $favoriteRepository;

    public function __construct(ShowroomFavoriteRepository $favoriteRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
    }

    public function addFavorite($userId, $showroomId)
    {
        if (!Showroom::find($showroomId)) {
            return [
                'status' => 'error',
                'message' => 'The selected showroom does not exist.'
            ];
        }

        if (!$this->favoriteRepository->exists($userId, $showroomId)) {
            $this->favoriteRepository->add($userId, $showroomId);
            return [
                'status' => 'success',
                'message' => 'Showroom has been added to favorites.'
            ];
        }

        return [
            'status' => 'info',
            'message' => 'Showroom is already in your favorites.'
        ];
    }

    public function removeFavorite($userId, $showroomId)
    {
        if ($this->favoriteRepository->exists($userId, $showroomId)) {
            $this->favoriteRepository->remove($userId, $showroomId);
            return [
                'status' => 'success',
                'message' => 'Showroom has been removed from favorites.'
            ];
        }

        return [
            'status' => 'warning',
            'message' => 'Showroom was not found in your favorites.'
        ];
    }

    public function listFavorites($userId)
    {
        return $this->favoriteRepository->getAllForUser($userId);
    }
}
