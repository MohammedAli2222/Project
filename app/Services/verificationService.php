<?php

namespace App\Services;

use App\Models\Showroom;
use App\Models\User;
use App\Models\verification;
use Illuminate\Http\Request;
use App\Repositories\VerificationRepository;
use Illuminate\Support\Facades\Auth;
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

        $destinationPath = public_path('verifications');

        $document->move($destinationPath, $documentName);

        $documentPath = 'verifications/' . $documentName;

        $created = $this->verificationRepository->createUserVerification($user->id, $documentPath);


        if ($created === false) {
            return response()->json([
                'status' => 0,
                'message' => 'Verification already exists.'
            ], 409);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Verification submitted successfully.'
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

        $documentPath = $request->file('document')->store('verifications', 'public');

        $created = $this->verificationRepository->createShowroomVerification($request->showroom_id, $documentPath);

        if ($created === false) {
            return response()->json([
                'status' => 0,
                'message' => 'Verification already exists.'
            ], 409);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Verification submitted successfully.'
        ]);
    }
    public function getStatusVerification()
    {

        $user = Auth::user();

        return $this->verificationRepository->getStatus($user);
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
    public function updateStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:Pending,Approved,Rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updatedVerification = $this->verificationRepository->changeStatus($id, $request->status);

        if (!$updatedVerification) {
            return response()->json([
                'status' => false,
                'message' => 'Verification record not found or could not be updated.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Verification status updated successfully.',
            'data' => [
                'id' => $updatedVerification->id,
                'new_status' => $updatedVerification->status,
                'type' => $updatedVerification->type
            ]
        ]);
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


    // public function approveUserVerification($verificationId)
    // {
    //     $verification = $this->verificationRepository->approveUserVerification($verificationId);

    //     if (!$verification) {
    //         return [
    //             'status' => false,
    //             'message' => 'Verification not found or not a user verification'
    //         ];
    //     }

    //     return [
    //         'status' => true,
    //         'message' => 'User verification approved and role updated to OfficeOwner',
    //         'verification' => $verification
    //     ];
    // }

    // public function rejectUserVerification($verificationId)
    // {
    //     $verification = $this->verificationRepository->rejectUserVerification($verificationId);

    //     if (!$verification) {
    //         return [
    //             'status' => false,
    //             'message' => 'Verification not found or not a user verification'
    //         ];
    //     }

    //     return [
    //         'status' => true,
    //         'message' => 'User verification rejected',
    //         'verification' => $verification
    //     ];
    // }

    // public function approveShowroomVerification($verificationId)
    // {
    //     $updated = $this->verificationRepository->approveShowroomVerification($verificationId);

    //     return [
    //         'status' => (bool)$updated,
    //         'message' => $updated ? 'تم توثيق المعرض بنجاح' : 'طلب التوثيق غير صالح',
    //         'data' => $updated ? verification::find($verificationId) : null
    //     ];
    // }

    // public function rejectShowroomVerification($verificationId, $reason = null)
    // {
    //     $updated = $this->verificationRepository->rejectShowroomVerification($verificationId, $reason);

    //     return [
    //         'status' => (bool)$updated,
    //         'message' => $updated ? 'تم رفض توثيق المعرض' : 'طلب التوثيق غير صالح',
    //         'data' => $updated ? Verification::find($verificationId) : null
    //     ];
    // }


    public function updateVerificationStatus(int $verificationId, string $status): array
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            return [
                'status' => false,
                'message' => 'Invalid status value.'
            ];
        }

        $verification = $this->verificationRepository->findVerificationById($verificationId);

        if (!$verification) {
            return [
                'status' => false,
                'message' => 'Verification request not found. '
            ];
        }

        $verification->status = $status;
        $verification->save();

        if ($verification-> type === User::class) {
            $message = $status === 'approved'
                ? 'User verification approved successfully.'
                : 'User verification rejected.';
        } elseif ($verification-> type === Showroom::class) {
            $message = $status === 'approved'
                ? 'Showroom verification approved successfully.'
                : 'Showroom verification rejected.';
        } else {
            $message = 'Verification updated.';
        }

        return [
            'status' => true,
            'message' => $message,
            'verification_id' => $verification->id
        ];
    }
}
