<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserResource;

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
            $path = $data['profile_picture']->store('profile_pictures', 'public');
            $data['profile_picture'] = 'storage/' . $path;
        } else {
            $data['profile_picture'] = null;
        }

        if (!isset($data['role_id']))
            $data['role_id'] = Role::USER_ROLE_ID;

        $data['password'] = Hash::make($data['password']);

        $user = $this->AuthRepo->create($data);

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully.',
            'user'    => new UserResource($user)
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
            'email' => $user->email,
        ];
    }


    public function logout()
    {
        $user = auth()->user();

        return $this->AuthRepo->removeTokens($user);
    }

    public function profile(): JsonResponse
    {
        $user = $this->AuthRepo->getProfile();

        return response()->json([
            'status' => true,
            'user' => new UserResource($user)
        ]);
    }

    public function editProfile(array $newData): array
    {
        $user = auth()->user();

        if (!$user) {
            return [
                'status' => false,
                'message' => 'Unauthorized.'
            ];
        }

        if (isset($newData['profile_picture']) && $newData['profile_picture'] instanceof UploadedFile) {
            $image = $newData['profile_picture'];

            if ($user->profile_picture) {
                $oldPath = str_replace('storage/', '', $user->profile_picture);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $extension = $image->getClientOriginalExtension();
            $fileName = 'user_' . $user->id . '.' . $extension;

            $path = $image->storeAs('profile_pictures', $fileName, 'public');

            $newData['profile_picture'] = 'storage/' . $path;
        } else {
            unset($newData['profile_picture']);
        }

        if (isset($newData['password']) && !empty($newData['password'])) {
            if (!isset($newData['old_password']) || !Hash::check($newData['old_password'], $user->password)) {
                return [
                    'status' => false,
                    'message' => 'Old password is incorrect or missing.'
                ];
            }
            $newData['password'] = Hash::make($newData['password']);
        } else {
            unset($newData['password']);
        }

        $updatedUser = $this->AuthRepo->newData($user, $newData);

        return [
            'status' => true,
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($updatedUser)
        ];
    }

    public function getUserWithRole(int $userId): array
    {
        $user = $this->AuthRepo->getUserWithRole($userId);

        if (!$user) {
            return [
                'status'  => false,
                'message' => 'User not found.',
            ];
        }

        return [
            'status' => true,
            'data'   => [
                'id'              => $user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'user_name'       => $user->user_name,
                'profile_picture' => $user->profile_picture,
                'role_id'         => $user->role_id,
            ],
        ];
    }
}
