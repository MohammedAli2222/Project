<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Http\Requests\RegisterRequest;


class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        return $this->authService->register($validatedData);
    }


    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $response = $this->authService->login($credentials);

        return response()->json($response, $response['status'] ? 200 : 401);
    }


    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ]);
    }


    public function profile(): JsonResponse
    {
        $user = $this->authService->profile();

        return response()->json([
            'status' => true,
            'user'   => $user
        ]);
    }

    public function editProfile(Request $request): JsonResponse
    {
        $data = $request->only(['name', 'email', 'number_phone', 'password', 'profile_picture']);

        $user = $this->authService->editProfile($data);

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user
        ]);
    }
}
