<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
        $histories = History::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get(['car_id', 'action', 'created_at']);

        return response()->json([
            'status' => true,
            'data' => $histories
        ]);
    }
}

