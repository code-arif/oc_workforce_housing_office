<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\WorkScheduleRequest;
use App\Http\Controllers\Api\Auth\AuthenticationController;

//health-check
Route::get("/check", function () {
    return "All Right 👍";
});

//Guest user routes
Route::group(['middleware' => 'guest:api'], function () {

    // Login & Register
    Route::post('/login', [AuthenticationController::class, 'login']);
});


Route::group(['middleware' => 'auth:api'], function () {
    //User logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    //employee list
    Route::get('/employee-list', [UserListController::class, 'index']);

    // Work reschedule request
    Route::post('/reschedule-request', [WorkScheduleRequest::class, 'upsert']); // working
    Route::get('/reschedule-request/edit/{id}', [WorkScheduleRequest::class, 'edit']); // working
    Route::delete('/reschedule-request/delete/{id}', [WorkScheduleRequest::class, 'destroy']); // working
    Route::post('/self-reschedule/{id}', [WorkScheduleRequest::class, 'selfReschedule']); // working


    // Work manage
    Route::group(['prefix' => 'work'], function () {
        Route::get('/list', [WorkController::class, 'index']);
        Route::get('/map', [WorkController::class, 'mapView']);
        Route::post('/complete/{id}', [WorkController::class, 'completeWork']); // work complation
        Route::post('/incomplete/{id}', [WorkController::class, 'inCompleteWork']); // work imcomplation
        Route::get('/details/{id}', [WorkController::class, 'show']); // working
    });


    Route::post('/location/update', [LocationController::class, 'update']);
    Route::get('/locations/current', [LocationController::class, 'getCurrentLocations']);
    Route::get('/team/{teamId}/history', [LocationController::class, 'getTeamHistory']);
});
