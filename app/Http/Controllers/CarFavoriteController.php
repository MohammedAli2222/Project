<?php

namespace App\Http\Controllers;


use App\Services\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class CarFavoriteController extends Controller
{
    protected FavoriteService $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function add($car_id)
    {
        $userId = auth()->user()->id;
        $result = $this->favoriteService->addFavorite($userId, $car_id);

        return response()->json(['status' => $result]);
    }

    public function remove($car_id)
    {
        $userId = auth()->user()->id;
        $result = $this->favoriteService->removeFavorite($userId, $car_id);

        return response()->json(['status' => $result]);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $favorites = $this->favoriteService->listFavorites($userId);

        return response()->json($favorites);
    }
}
