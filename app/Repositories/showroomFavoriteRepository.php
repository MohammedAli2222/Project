<?php

namespace App\Repositories;

use App\Models\showroom_favorite;

class ShowroomFavoriteRepository
{
    public function add($userId, $showroomId)
    {
        return showroom_favorite::firstOrCreate([
            'user_id' => $userId,
            'showroom_id' => $showroomId
        ]);
    }

    public function remove($userId, $showroomId)
    {
        return showroom_favorite::where('user_id', $userId)
            ->where('showroom_id', $showroomId)
            ->delete();
    }

    public function exists($userId, $showroomId)
    {
        return showroom_favorite::where('user_id', $userId)
            ->where('showroom_id', $showroomId)
            ->exists();
    }

    public function getAllForUser($userId)
    {
        return showroom_favorite::with('showroom')
            ->where('user_id', $userId)
            ->get();
    }
}
