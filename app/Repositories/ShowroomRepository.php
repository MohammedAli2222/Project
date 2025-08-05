<?php

namespace App\Repositories;

use App\Models\Showroom;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShowroomRepository
{

    public function create(array $data)
    {
        return Showroom::create($data);
    }
    public function findShowroom(int $id)
    {
        $userID = Auth::user()->id;
        return Showroom::where('user_id', $userID)->where('id', $id)->first();
    }
    public function getAll($userID)
    {
        return Showroom::where('user_id', $userID)->orderByDesc('created_at')->get();
    }
    public function delete(int $id): bool
    {
        $userId = auth()->user()->id;

        $showroom = Showroom::where('user_id', $userId)->find($id);

        return $showroom ? $showroom->delete() : false;
    }



    public function find(int $id)
    {
        $userID = auth()->user()->id;
        return Showroom::where('user_id', $userID)->where('id', $id)->first();
    }

    public function saveNewData(Showroom $showroom, array $newData): Showroom
    {
        $showroom->name     = $newData['name']     ?? $showroom->name;
        $showroom->location = $newData['location'] ?? $showroom->location;
        $showroom->phone    = $newData['phone']    ?? $showroom->phone;

        if (isset($newData['logo']) && $newData['logo'] instanceof UploadedFile) {

            if ($showroom->logo) {
                Storage::disk('public')->delete($showroom->logo);
            }

            $showroom->logo = $this->storeLogo($newData['logo']);
        } elseif (array_key_exists('logo', $newData) && $newData['logo'] === null) {

            if ($showroom->logo) {
                Storage::disk('public')->delete($showroom->logo);
            }
            $showroom->logo = null;
        }

        $showroom->save();
        return $showroom;
    }

    private function storeLogo(UploadedFile $file)
    {
        return $file->store('showroom_logos', 'public');
    }
}
