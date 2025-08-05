<?php

namespace App\Repositories;

use App\Models\Role;
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

    public function getStatus(User $user)
    {

        $status = Verification::where('user_id', $user->id)->first();
        return [
            'status' => $status ? $status->status : null,
        ];
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
    // public function approveUserVerification($verificationId)
    // {
    //     $verification = Verification::find($verificationId);

    //     if (!$verification || $verification->type !== 'USER') {
    //         return false;
    //     }

    //     $verification->status = 'Approved';
    //     $verification->save();

    //     $user = $verification->user;
    //     $officeOwnerRole = Role::where('role', 'OfficeOwner')->first();

    //     if ($user && $officeOwnerRole) {
    //         $user->role_id = $officeOwnerRole->id;
    //         $user->save();
    //     }

    //     return $verification;
    // }
    // public function rejectUserVerification($verificationId)
    // {
    //     $verification = Verification::find($verificationId);

    //     if (!$verification || $verification->type !== 'USER') {
    //         return false;
    //     }

    //     $verification->status = 'Rejected';
    //     return $verification->save();
    // }
    // public function approveShowroomVerification($verificationId)
    // {
    //     $verification = Verification::find($verificationId);

    //     if (!$verification || $verification->type !== 'Showroom') {
    //         return false;
    //     }

    //     $verification->status = 'Approved';
    //     return $verification->save();
    // }
    // public function rejectShowroomVerification($verificationId, $reason = null)
    // {
    //     $verification = Verification::find($verificationId);

    //     if (!$verification || $verification->type !== 'Showroom') {
    //         return false;
    //     }

    //     $verification->status = 'Rejected';
    //     $verification->rejection_reason = $reason;
    //     return $verification->save();
    // }

    // public function isShowroomVerified($showroomId)
    // {
    //     return Verification::where('showroom_id', $showroomId)
    //            ->where('status', 'Approved')
    //            ->exists();
    // }




}
