<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShowroomFavoriteService;
use Illuminate\Routing\Controller;

class ShowroomFavoriteController extends Controller
{
    protected ShowroomFavoriteService $service;

    public function __construct(ShowroomFavoriteService $service)
    {
        $this->service = $service;
    }

    public function add(Request $request, $showroom_id)
    {
        $response = $this->service->addFavorite($request->user()->id, $showroom_id);
        return response()->json($response, $response['status'] === 'success' ? 200 : 400);
    }

    public function remove(Request $request, $showroom_id)
    {
        $response = $this->service->removeFavorite($request->user()->id, $showroom_id);
        return response()->json($response, $response['status'] === 'success' ? 200 : 400);
    }

    public function index(Request $request)
    {
        $favorites = $this->service->listFavorites($request->user()->id);
        return response()->json($favorites);
    }
}
