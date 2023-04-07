<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\AdminAuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function () {
    Route::prefix('admin')->group(function () {
        // Route::post('register', [AdminAuthController::class, 'register']);
        Route::post('login', [AdminAuthController::class, 'login']);

        Route::middleware('jwt-admin')->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::get('user-list', [AdminAuthController::class, 'getUsers']);
        });
    });

    Route::prefix('user')->group(function () {
        // Route::post('register', [UserAuthController::class, 'register']);
        Route::post('login', [UserAuthController::class, 'login']);

        Route::middleware('jwt')->group(function () {
            Route::post('logout', [UserAuthController::class, 'logout']);

        });
    });

    Route::prefix('order')->group(function () {
        Route::middleware('jwt')->group(function () {
            Route::post('/create', [OrderController::class, 'store']);
            Route::get('/{uuid}', [OrderController::class, 'show']);
            Route::put('/{uuid}', [OrderController::class, 'update']);
            Route::delete('/{uuid}', [OrderController::class, 'destroy']);
        });
    });

    Route::prefix('orders')->group(function () {
        Route::middleware('jwt')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
        });
    });
});
