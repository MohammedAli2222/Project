<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ShowroomFavoriteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarFavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PersonalCarController;
use App\Http\Controllers\ShowroomController;
use App\Http\Controllers\VerificationController;

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
Route::get('random', [CarController::class, 'getRandomCars']);
Route::get('/cars', [CarController::class, 'allCars']);
Route::get('/allShowroom', [ShowroomController::class, 'allShowroom']);



Route::group(["middleware" => ["auth:sanctum"]], function () {
    // Auth
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/editProfile', [AuthController::class, 'editProfile']);

    //Favorite
    Route::post('/cars/{car}/favorite', [CarFavoriteController::class, 'add']);
    Route::delete('/cars/{car}/favorite', [CarFavoriteController::class, 'remove']);
    Route::get('/favorites/cars', [CarFavoriteController::class, 'index']);

    Route::post('/showrooms/{showroom}/favorite', [ShowroomFavoriteController::class, 'add']);
    Route::delete('/showrooms/{showroom}/favorite', [ShowroomFavoriteController::class, 'remove']);
    Route::get('/favorites/showrooms', [ShowroomFavoriteController::class, 'index']);

    //History
    Route::get('/history', [HistoryController::class, 'index']);



    // Verification
    Route::prefix('verify')->group(function () {
        Route::post('/user', [VerificationController::class, 'userVerification'])->middleware('check_User');
        Route::post('/showroom', [VerificationController::class, 'showroomVerification'])->middleware('check_OfficeOwner');
        Route::get('/status', [VerificationController::class, 'getVerificationStatus']);
        Route::middleware('check_Admin')->group(function () {
            Route::get('/status/{id}', [VerificationController::class, 'showVerificationDetails']);
            Route::get('/allUsers', [VerificationController::class, 'showaAllVerificationUser']);
            Route::get('/allShowroom', [VerificationController::class, 'showaAllVerificationShowroom']);
            Route::get('/pending', [VerificationController::class, 'getPendingVerifications']);
            Route::get('/pending/users', [VerificationController::class, 'getPendingUserVerifications']);
            Route::get('/pending/showrooms', [VerificationController::class, 'getPendingShowroomVerifications']);
            Route::post('update', [VerificationController::class, 'update']);
        });
    });



    // Showroom
    Route::middleware('check_OfficeOwner')->group(function () {
        Route::post('/addShowroom', [ShowroomController::class, 'addShowroom']);
        Route::get('/showroom/{id}', [ShowroomController::class, 'getShowroom']);
        Route::get('/showrooms', [ShowroomController::class, 'getShowrooms']);
        Route::delete('/deletShowrooms/{id}', [ShowroomController::class, 'deleteShowroom']);
        Route::put('/editShowrooms/{id}', [ShowroomController::class, 'editShowroom']);
    });

    //Car
    Route::prefix('car')->middleware('auth:sanctum')->group(function () {
        Route::post('/add/{showroom_id}', [CarController::class, 'addCar'])->middleware('check_OfficeOwner');
        Route::delete('/delete/{showroom_id}/{car_id}', [CarController::class, 'deleteCar'])->middleware('check_OfficeOwner');
        Route::get('/get/{showroom_id}/{car_id}', [CarController::class, 'getCar']);
        Route::get('/getCars/{showroom_id}', [CarController::class, 'listCarsByShowroom']);
        Route::get('/{carID}/status/{status}', [CarController::class, 'changeCarStatus'])->middleware('check_OfficeOwner');
        Route::post('/update/{car}', [CarController::class, 'updateCar'])->middleware('check_OfficeOwner');
    });

    //Rental
    Route::prefix('rentals')->group(function () {
        Route::post('/mark-rentable', [RentalController::class, 'markCarAsRentable']);
        Route::post('/', [RentalController::class, 'createRental']);
        Route::post('/{id}/confirm', [RentalController::class, 'confirmRental']);
        Route::get('/user', [RentalController::class, 'getUserRentals']);
        Route::get('/showroom/{showroomId}', [RentalController::class, 'getShowroomRentals']);
        Route::get('/{rentalId}', [RentalController::class, 'getRentalDetails']);
    });

    //PersonalCar
    Route::get('/personal-cars', [PersonalCarController::class, 'index']);
    Route::get('/personal-cars/{id}', [PersonalCarController::class, 'show']);
    Route::post('/personal-cars', [PersonalCarController::class, 'store']);
    Route::put('/personal-cars/{id}', [PersonalCarController::class, 'update']);
    Route::delete('/personal-cars/{id}', [PersonalCarController::class, 'destroy']);

    //Auction
    Route::prefix('auction')->middleware('auth:sanctum')->group(function () {
        Route::post('/create', [AuctionController::class, 'createAuction']);
        Route::put('/{id}/update', [AuctionController::class, 'updateAuction']);
        Route::delete('/{id}/delete', [AuctionController::class, 'deleteAuction']);

        Route::post('/{id}/cancel', [AuctionController::class, 'cancelAuction']);
        Route::get('/active', [AuctionController::class, 'getActiveAuctions']);
        Route::get('/{id}', [AuctionController::class, 'getAuction']);

        Route::get('/{id}/bids', [AuctionController::class, 'getBids']);
        Route::get('/showroom/{id}', [AuctionController::class, 'getShowroomAuctions']);
        Route::post('/bid/{id}', [AuctionController::class, 'placeBid']);
        Route::post('/{id}/close', [AuctionController::class, 'closeAuction']);
    });
});
