<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShowroomRequest;
use App\Services\ShowroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ShowroomController extends Controller
{
    protected ShowroomService $showroomService;

    public function __construct(ShowroomService $showroomService)
    {
        $this->showroomService = $showroomService;
    }

    public function addShowroom(ShowroomRequest $request): JsonResponse
    {
        $response = $this->showroomService->store($request->validated());
        return response()->json($response, $response['status']);
    }

    public function getShowroom(int $id): JsonResponse
    {
        $response = $this->showroomService->getShowroomById($id);

        if ($response['status'] === 404) {
            return response()->json(['message' => $response['message']], 404);
        }

        return response()->json(['data' => $response['data']], 200);
    }

    public function getShowrooms(): JsonResponse
    {
        $response = $this->showroomService->getAllShowrooms();
        return response()->json(['data' => $response['data']], 200);
    }

    public function deleteShowroom(int $id): JsonResponse
    {
        $response = $this->showroomService->deleteShowroom($id);
        return response()->json(['message' => $response['message']], $response['status']);
    }

    public function editShowroom(int $id, ShowroomRequest $request): JsonResponse
    {
        $response = $this->showroomService->editShowroom($id, $request->validated());
        return response()->json($response, $response['status']);
    }
}
