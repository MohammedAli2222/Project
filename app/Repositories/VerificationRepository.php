<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Verification;

class VerificationRepository
{
    public function createUserVerification($userId, $documentPath)
    {

        $exists = Verification::where('user_id', $userId)->where('type', 'USER')->first();

        if ($exists) return false;

        return Verification::create([
            'user_id' => $userId,
            'type' => 'USER',
            'document_path' => $documentPath,
            'status' => 'Pending',
        ]);
    }
    public function createShowroomVerification($showroomId, $documentPath)
    {
        $exists = Verification::where('showroom_id', $showroomId)->where('type', 'Showroom')->first();
        if ($exists) return false;

        return Verification::create([
            'showroom_id' => $showroomId,
            'type' => 'Showroom',
            'document_path' => $documentPath,
            'status' => 'Pending',
        ]);
    }
    public function getShowroomStatus($showroomId)
    {
        $status = Verification::where('showroom_id', $showroomId)->first();

        return $status ? $status->status : 'Not submitted';
    }
    public function findVerificationById($id)
    {
        return Verification::find($id);
    }
    public function findVerificationUsers()
    {
        return Verification::where('type', 'USER')->get();
    }
    public function findVerificationShowrooms()
    {
        return Verification::where('type', 'showroom')->get();
    }
    public function findPendingVerifications()
    {
        return Verification::where('status', 'Pending')
            ->with(['user', 'showroom']) // تحميل العلاقات
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function findPendingUserVerifications()
    {
        return Verification::where('status', 'Pending')
            ->where('type', 'USER')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function findPendingShowroomVerifications()
    {
        return Verification::where('status', 'Pending')
            ->where('type', 'Showroom')
            ->with('showroom')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function changeStatus($id, $newStatus)
    {
        $verification = Verification::find($id);

        if (!$verification) {
            return false;
        }
        $verification->status = $newStatus;
        $verification->save();

        return $verification;
    }

    public function getUserVerificationStatus($userId): ?bool
    {
        $user = User::find($userId);

        return $user ? (bool) $user->is_verif : null;
    }
}
