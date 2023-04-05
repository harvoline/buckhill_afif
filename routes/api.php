<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;

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
        Route::post('register', [AdminController::class, 'register']);
        Route::post('login', [AdminController::class, 'login']);
        Route::post('logout', [AdminController::class, 'logout']);
        // Route::post('register', 'AdminController@register');
        // Route::post('login', 'AdminController@login');
        // Route::post('logout', 'AdminController@logout');

        Route::middleware('jwt-admin')->group(function () {
            Route::get('users', [AdminController::class, 'getUsers']);
            Route::put('users/{user}', [AdminController::class, 'updateUser']);
            Route::delete('users/{user}', [AdminController::class, 'deleteUser']);
            // Route::get('users', 'AdminController@getUsers');
            // Route::put('users/{user}', 'AdminController@updateUser');
            // Route::delete('users/{user}', 'AdminController@deleteUser');
        });
    });
});
