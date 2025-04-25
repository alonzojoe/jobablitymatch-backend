<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DisabilityTypeController;


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
    Route::post('/create', [RoleController::class, 'store']);
    Route::patch('/update/{id}', [RoleController::class, 'update']);
    Route::patch('/destroy/{id}', [RoleController::class, 'destroy']);
});


Route::group(['prefix' => '/company'], function () {
    Route::get('/', [CompanyController::class, 'index']);
    Route::post('/create', [CompanyController::class, 'store']);
    Route::patch('/update/{id}', [CompanyController::class, 'update']);
    Route::patch('/destroy/{id}', [CompanyController::class, 'destroy']);
});


Route::group(['prefix' => '/user'], function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/update/{id}', [UserController::class, 'update']);
    Route::patch('/destroy/{id}', [UserController::class, 'destroy']);
});

Route::group(['prefix' => '/disability'], function () {
    Route::get('/', [DisabilityTypeController::class, 'index']);
    Route::get('/{id}', [DisabilityTypeController::class, 'show']);
    Route::post('/create', [DisabilityTypeController::class, 'store']);
    Route::put('/update/{id}', [DisabilityTypeController::class, 'update']);
    Route::patch('/destroy/{id}', [DisabilityTypeController::class, 'destroy']);
});
