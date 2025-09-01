<?php

namespace App\Http\Controllers;

use App\Models\verification;
use Illuminate\Http\Request;
use App\Services\VerificationService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }
    public function userVerification(Request $request)
    {
        return $this->verificationService->UserVerification($request);
    }
    public function showroomVerification(Request $request)
    {
        return $this->verificationService->ShowroomVerification($request);
    }
    public function getVerificationStatus()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$user->role) {
            return response()->json([
                'status' => false,
                'message' => 'User role not found.'
            ], 400);
        }

        $record = null;
        $verificationType = null;

        if ($user->role->role === 'User') {
            $record = Verification::where('user_id', $user->id)
                ->where('type', 'USER')->first();
            $verificationType = 'USER';
        } elseif ($user->role->role === 'OfficeOwner') {
            $showroom = $user->showrooms->first();

            if (!$showroom) {
                return response()->json([
                    'status' => false,
                    'message' => 'OfficeOwner does not own any registered showrooms.'
                ], 404);
            }

            $record = Verification::where('showroom_id', $showroom->id)
                ->where('type', 'Showroom')->first();
            $verificationType = 'Showroom';
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid role for verification status check.'
            ], 400);
        }

        $status = $record->status ?? 'Not Submitted';
        $document = $record->document_path ?? null;

        return response()->json([
            'status' => true,
            'verification' => [
                'type'     => $verificationType,
                'status'   => $status,
                'document' => $document ? Storage::url($document) : null,
            ]
        ]);
    }
    public function showVerificationDetails($id)
    {
        $response = $this->verificationService->getVerificationDetails($id);
        if (!$response['status']) {
            return response()->json($response, 404);
        }
        return response()->json($response, 200);
    }
    public function showaAllVerificationUser()
    {
        $response = $this->verificationService->getVerificationUsers();

        if (!$response['status']) {
            return response()->json($response, 404);
        }
        return response()->json($response, 200);
    }
    public function showaAllVerificationShowroom()
    {
        $response = $this->verificationService->getVerificationShowroom();

        if (!$response['status']) {
            return response()->json($response, 404);
        }
        return response()->json($response, 200);
    }
    public function getPendingVerifications()
    {
        $response = $this->verificationService->getPendingVerifications();

        if (!$response['status']) {
            return response()->json($response, 404);
        }

        return response()->json($response, 200);
    }
    public function getPendingUserVerifications()
    {
        $response = $this->verificationService->getPendingUserVerifications();

        if (!$response['status']) {
            return response()->json($response, 404);
        }

        return response()->json($response, 200);
    }
    public function getPendingShowroomVerifications()
    {
        $response = $this->verificationService->getPendingShowroomVerifications();

        if (!$response['status']) {
            return response()->json($response, 404);
        }

        return response()->json($response, 200);
    }
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|in:Approved,Rejected',
        ]);

        $result = $this->verificationService->updateVerificationStatus($request->id, $request->status);

        return response()->json($result, $result['status'] ? 200 : 400);
    }
}
