<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;

Route::get('/test', function () {
    return response()->json(['status' => 'success', 'message' => 'API Endpoint Works!'], 200);
});



Route::group(['prefix' => '/auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

Route::group(['prefix' => '/role'], function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/store', [RoleController::class, 'store']);
    Route::patch('/update/{id}', [RoleController::class, 'update']);
    Route::patch('/destroy/{id}', [RoleController::class, 'destroy']);
});
