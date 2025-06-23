<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('sign-in', action: [AuthController::class, 'Register']);
Route::post('login', [AuthController::class, 'login']);


Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/edit', [AuthController::class, 'editProfile']);
});
