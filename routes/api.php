<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DisabilityTypeController;
use App\Http\Controllers\API\JobPostingController;
use App\Http\Controllers\API\ApplicantController;


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
    Route::get('/all', [RoleController::class, 'all']);
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
    Route::get('/all', [DisabilityTypeController::class, 'all']);
    Route::get('/', [DisabilityTypeController::class, 'index']);
    Route::get('/{id}', [DisabilityTypeController::class, 'show']);
    Route::post('/create', [DisabilityTypeController::class, 'store']);
    Route::put('/update/{id}', [DisabilityTypeController::class, 'update']);
    Route::patch('/destroy/{id}', [DisabilityTypeController::class, 'destroy']);
});

Route::group(['prefix' => '/posting'], function () {
    Route::get('/list', [JobPostingController::class, 'list']);
    Route::get('/', [JobPostingController::class, 'index']);
    Route::get('/recommended/{user_id}', [JobPostingController::class, 'recommended']);
    Route::get('/{id}', [JobPostingController::class, 'show']);
    Route::get('/company/{id}', [JobPostingController::class, 'getByCompany']);
    Route::post('/create', [JobPostingController::class, 'store']);
    Route::put('/update/{id}', [JobPostingController::class, 'update']);
    Route::patch('/destroy/{id}', [JobPostingController::class, 'destroy']);
    Route::patch('/inactive/{id}', [JobPostingController::class, 'inactive']);
});

Route::group(['prefix' => '/applicant'], function () {
    Route::get('/', [ApplicantController::class, 'getByJobPosting']);
    Route::get('/{id}', [ApplicantController::class, 'show']);
    Route::patch('/update/{id}', [ApplicantController::class, 'update']);
    Route::patch('/destroy/{id}', [ApplicantController::class, 'destroy']);
});
