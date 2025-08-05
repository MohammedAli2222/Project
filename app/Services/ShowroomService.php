<?php

namespace App\Services;

use App\Http\Resources\ShowroomResource;
use App\Repositories\ShowroomRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShowroomService
{
    protected ShowroomRepository $showroomRepository;

    public function __construct(ShowroomRepository $showroomRepository)
    {
        $this->showroomRepository = $showroomRepository;
    }

    public function store(array $data)
    {
        $user = Auth::user();

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $data['logo'] = $this->storeLogo($data['logo']);
        } else {
            $data['logo'] = null;
        }

        $data['user_id'] = $user->id;
        $showroom = $this->showroomRepository->create($data);

        return [
            'status' => 201,
            'data' => new ShowroomResource($showroom),
            'message' => 'Showroom created successfully'
        ];
    }

    public function getShowroomById(int $id)
    {
        $showroom = $this->showroomRepository->findShowroom($id);

        if (!$showroom) {
            return [
                'status' => 404,
                'message' => 'Showroom not found'
            ];
        }

        return [
            'status' => 200,
            'data' => new ShowroomResource($showroom)
        ];
    }

    public function getAllShowrooms()
    {
        $userID = Auth::id();
        $showrooms = $this->showroomRepository->getAll($userID);

        return [
            'status' => 200,
            'data' => ShowroomResource::collection($showrooms)
        ];
    }

    public function deleteShowroom(int $id)
    {
        $deleted = $this->showroomRepository->delete($id);

        if (!$deleted) {
            return [
                'status' => 404,
                'message' => 'Showroom not found'
            ];
        }

        return [
            'status' => 200,
            'message' => 'Showroom deleted successfully'
        ];
    }

    public function editShowroom(int $id, array $data)
    {
        $showroom = $this->showroomRepository->find($id);

        if (!$showroom) {
            return [
                'status' => 404,
                'message' => 'Showroom not found'
            ];
        }

        $updatedShowroom = $this->showroomRepository->saveNewData($showroom, $data);

        return [
            'status' => 200,
            'data' => new ShowroomResource($updatedShowroom),
            'message' => 'Showroom updated successfully'
        ];
    }

    private function storeLogo(UploadedFile $file)
    {
        return $file->store('showroom_logos', 'public');
    }
}
