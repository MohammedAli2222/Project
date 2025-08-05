<?php

namespace App\Repositories;

use App\Models\User;


class AuthRepository
{
    public function create(array $data)
    {
        return User::create($data);
    }
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }
    public function removeTokens(User $user)
    {

        $user->tokens()->delete();
    }
    public function getProfile()
    {

        $user = auth()->user();

        return $user;
    }
    public function newData(User $user, $newData)
    {
        $user->first_name = $newData['first_name'] ?? $user->first_name;
        $user->last_name  = $newData['last_name'] ?? $user->last_name;
        $user->user_name  = $newData['user_name'] ?? $user->user_name;
        $user->email      = $newData['email'] ?? $user->email;
        $user->phone      = $newData['phone'] ?? $user->phone;

        if (isset($newData['password'])) {
            $user->password = $newData['password'];
        }

        if (isset($newData['profile_picture'])) {
            $user->profile_picture = $newData['profile_picture'];
        }

        $user->save();

        return $user;
    }
}
