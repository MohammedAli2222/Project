<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 0,
                'message' => 'Unauthenticated.'
            ], 401);
        }


        $userRoleName = auth()->user()->role->role ?? null;

        if ($userRoleName === 'Admin') {
            return $next($request);
        }

        return response()->json([
            'status' => 0,
            'message' => 'You do not have permission to access this resource.'
        ], 403); 
    }
}
