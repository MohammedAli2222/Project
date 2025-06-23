<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;

class AuthService
{
    protected AuthRepository $AuthRepo;

    public function __construct(AuthRepository $userRepo)
    {
        $this->AuthRepo = $userRepo;
    }

    public function register(array $data): JsonResponse
    {
        if (isset($data['profile_picture']) && $data['profile_picture'] instanceof UploadedFile) {
            $image = $data['profile_picture'];
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('profile_pictures'), name: $imageName);
            $data['profile_picture'] = $imageName;
        } else {
            $data['profile_picture'] = null;
        }

        if (isset($data['prove_Admin']) && $data['prove_Admin'] instanceof UploadedFile) {
            $image = $data['prove_Admin'];
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('prove_Admins'), name: $imageName);
            $data['prove_Admin'] = $imageName;
        } else {
            $data['prove_Admin'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        $user = $this->AuthRepo->create($data);

        return response()->json([
            'status'  => true,
            'message' => 'Sucessfully.',
            'user'    => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'profile_picture' => $user->profile_picture,
            ]
        ]);
    }
    public function login(array $credentials): array
    {
        $user = $this->AuthRepo->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'status' => false,
                'message' => 'Invalid credentials.',
            ];
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'status' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture,
            ],
        ];
    }

    public function logout()
    {

        $user = auth()->user();

        return $this->AuthRepo->removeTokens($user);
    }

    public function profile()
    {

        return $this->AuthRepo->getProfile();
    }

    public function editProfile(array $credentials)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        } else {
            return $this->AuthRepo->newData($user, $credentials);
        }
    }
}
