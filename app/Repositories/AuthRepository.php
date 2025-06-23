<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

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
        $user->name         = $newData['name'] ?? $user->name;
        $user->email        = $newData['email'] ?? $user->email;
        $user->phone = $newData['phone'] ?? $user->phone;

        if (!empty($newData['password'])) {
            $user->password = Hash::make($newData['password']);
        }

        if (isset($newData['profile_picture']) && $newData['profile_picture'] instanceof UploadedFile) {
            if ($user->profile_picture) {
                $oldImagePath = public_path('profile_pictures/' . $user->profile_picture);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }

            $image = $newData['profile_picture'];
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('profile_pictures'), $imageName);
            $user->profile_picture = $imageName;
        }

        $user->save();
        return $user;
    }
}
