<?php

namespace App\Services;

use App\Models\Showroom;
use App\Models\User;
use App\Models\verification;
use Illuminate\Http\Request;
use App\Repositories\VerificationRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VerificationService
{
    protected $verificationRepository;

    public function __construct(VerificationRepository $verificationRepository)
    {
        $this->verificationRepository = $verificationRepository;
    }

    public function UserVerification(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'errors' => $validator->errors()
            ], 422);
        }

        $document = $request->file('document');
        $documentName = time() . '_' . $document->getClientOriginalName();

        $documentPath = $document->storeAs('verifications', $documentName, 'public');
        $created = $this->verificationRepository->createUserVerification($user->id, $documentPath);

        if ($created === false) {

            Storage::disk('public')->delete($documentPath);

            return response()->json([
                'status' => 0,
                'message' => 'Verification already exists.'
            ], 409);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Verification submitted successfully.',
            'file_url' => asset('storage/' . $documentPath)
        ]);
    }
    public function ShowroomVerification(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'showroom_id' => 'required|exists:showrooms,id',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'errors' => $validator->errors()
            ], 422);
        }

        $document = $request->file('document');
        $documentName = time() . '_' . $document->getClientOriginalName();

        $documentPath = $document->storeAs('verifications', $documentName, 'public');

        $created = $this->verificationRepository->createShowroomVerification($request->showroom_id, $documentPath);

        if ($created === false) {

            Storage::disk('public')->delete($documentPath);

            return response()->json([
                'status' => 0,
                'message' => 'Verification already exists.'
            ], 409);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Verification submitted successfully.',
            'file_url' => asset('storage/' . $documentPath)
        ]);
    }
    public function getVerificationDetails($id)
    {
        $verification = $this->verificationRepository->findVerificationById($id);
        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Verification record not found.'
            ];
        }
        return [
            'status' => true,
            'verification' => $verification
        ];
    }
    public function getVerificationUsers()
    {
        $verification = $this->verificationRepository->findVerificationUsers();
        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Verification record not found.'
            ];
        }
        return [
            'status' => true,
            'verification' => $verification
        ];
    }
    public function getVerificationShowroom()
    {
        $verification = $this->verificationRepository->findVerificationShowrooms();
        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Verification record not found.'
            ];
        }
        return [
            'status' => true,
            'verification' => $verification
        ];
    }
    public function getPendingVerifications()
    {
        $verifications = $this->verificationRepository->findPendingVerifications();

        if ($verifications->isEmpty()) {
            return [
                'status' => false,
                'message' => 'No pending verification requests found.'
            ];
        }

        // تنسيق البيانات للعرض
        $formattedVerifications = $verifications->map(function ($verification) {
            return [
                'id' => $verification->id,
                'type' => $verification->type,
                'status' => $verification->status,
                'document_path' => $verification->document_path,
                'document_url' => $verification->document_path ? asset('storage/' . $verification->document_path) : null,
                'created_at' => $verification->created_at->format('Y-m-d H:i:s'),
                'user' => $verification->user ? [
                    'id' => $verification->user->id,
                    'name' => $verification->user->name,
                    'email' => $verification->user->email
                ] : null,
                'showroom' => $verification->showroom ? [
                    'id' => $verification->showroom->id,
                    'name' => $verification->showroom->name,
                    'location' => $verification->showroom->location ?? null
                ] : null
            ];
        });

        return [
            'status' => true,
            'count' => $verifications->count(),
            'verifications' => $formattedVerifications
        ];
    }
    public function getPendingUserVerifications()
    {
        $verifications = $this->verificationRepository->findPendingUserVerifications();

        if ($verifications->isEmpty()) {
            return [
                'status' => false,
                'message' => 'No pending user verification requests found.'
            ];
        }

        $formattedVerifications = $verifications->map(function ($verification) {
            return [
                'id' => $verification->id,
                'type' => $verification->type,
                'status' => $verification->status,
                'document_path' => $verification->document_path,
                'document_url' => $verification->document_path ? asset('storage/' . $verification->document_path) : null,
                'created_at' => $verification->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $verification->user->id,
                    'name' => $verification->user->name,
                    'email' => $verification->user->email
                ]
            ];
        });

        return [
            'status' => true,
            'count' => $verifications->count(),
            'verifications' => $formattedVerifications
        ];
    }
    public function getPendingShowroomVerifications()
    {
        $verifications = $this->verificationRepository->findPendingShowroomVerifications();

        if ($verifications->isEmpty()) {
            return [
                'status' => false,
                'message' => 'No pending showroom verification requests found.'
            ];
        }

        $formattedVerifications = $verifications->map(function ($verification) {
            return [
                'id' => $verification->id,
                'type' => $verification->type,
                'status' => $verification->status,
                'document_path' => $verification->document_path,
                'document_url' => $verification->document_path ? asset('storage/' . $verification->document_path) : null,
                'created_at' => $verification->created_at->format('Y-m-d H:i:s'),
                'showroom' => [
                    'id' => $verification->showroom->id,
                    'name' => $verification->showroom->name,
                    'location' => $verification->showroom->location ?? null
                ]
            ];
        });

        return [
            'status' => true,
            'count' => $verifications->count(),
            'verifications' => $formattedVerifications
        ];
    }
    public function updateVerificationStatus(int $verificationId, string $status): array
    {
        $status = ucfirst(strtolower($status));

        if (!in_array($status, ['Approved', 'Rejected'])) {
            return [
                'status' => false,
                'message' => 'Invalid status value.'
            ];
        }


        $verification = $this->verificationRepository->findVerificationById($verificationId);

        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Verification request not found.'
            ];
        }
        $verification->status = $status;
        $verification->save();

        if ($status === 'Approved') {
            if ($verification->type === 'USER' && $verification->user) {
                $verification->user->role_id = 2;
                $verification->user->is_verif = true;
                $verification->user->save();
            }

            if ($verification->type === 'Showroom' && $verification->showroom) {
                $verification->showroom->is_verif = true;
                $verification->showroom->save();
            }
        }

        $message = match ($verification->type) {
            'USER' => $status === 'Approved' ? 'User verification approved successfully.' : 'User verification rejected.',
            'Showroom' => $status === 'Approved' ? 'Showroom verification approved successfully.' : 'Showroom verification rejected.',
            default => 'Verification updated.'
        };

        return [
            'status' => true,
            'message' => $message,
            'verification_id' => $verification->id
        ];
    }

    public function getIsVerifiedStatus(int $userId)
    {
        $status = $this->verificationRepository->getUserVerificationStatus($userId);

        if ($status === null) {
            return [
                'status' => false,
                'message' => 'User not found.'
            ];
        }

        return [
            'status' => true,
            'is_verified' => $status
        ];
    }

    public function approveUserVerification(int $verificationId): array
    {
        $verification = $this->verificationRepository->findVerificationById($verificationId);

        if (!$verification || $verification->type !== 'USER') {
            return [
                'status' => false,
                'message' => 'Verification request not found or not for a user.'
            ];
        }

        $this->verificationRepository->changeStatus($verificationId, 'Approved');

        if ($verification->user) {
            $verification->user->is_verif = true;
            $verification->user->save();
        }

        return [
            'status' => true,
            'message' => 'User verification approved successfully.',
            'verification_id' => $verification->id
        ];
    }

    public function rejectUserVerification(int $verificationId): array
    {
        $verification = $this->verificationRepository->findVerificationById($verificationId);
        if (!$verification || $verification->type !== 'USER') {
            return ['status' => false, 'message' => 'Verification request not found or not for a user.'];
        }

        $this->verificationRepository->changeStatus($verificationId, 'Rejected');

        return ['status' => true, 'message' => 'User verification rejected.', 'verification_id' => $verification->id];
    }
}
