<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenents\LandingController;
use App\Http\Controllers\Api\Tenents\TenantAuthController;
use App\Http\Controllers\Api\Tenents\TenantFormController;
use App\Http\Controllers\Api\Auth\AuthenticationController;

//health-check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String()
    ]);
});

//Guest user routes
Route::group(['middleware' => 'guest:api'], function () {

    // Login & Register
    Route::post('/login', [AuthenticationController::class, 'login']);

    // Property Creation - Unit/Room/Bed API
    Route::get('/unit/{unitId}/rooms', function ($unitId) {
        $unit = \App\Models\Unit::with('rooms')->find($unitId);
        if (!$unit) {
            return response()->json(['rooms' => []]);
        }
        return response()->json(['rooms' => $unit->rooms]);
    })->name('api.unit.rooms');

    Route::post('/rooms/beds', function (\Illuminate\Http\Request $request) {
        $roomId = $request->input('room_id');
        if (empty($roomId)) {
            return response()->json(['beds' => []]);
        }
        $beds = \App\Models\Bed::where('room_id', $roomId)->with('room')->get();
        return response()->json(['beds' => $beds]);
    })->name('api.rooms.beds');

    /*
    |--------------------------------------------------------------------------
    | Tenent Management Routes
    |--------------------------------------------------------------------------
    */
    // Public Routes
    Route::prefix('v1')->group(function () {

        // Landing - Tenant Email Submission
        Route::post('tenant/apply', [LandingController::class, 'submitEmail']);

        // Tenant Form (Token-based)
        Route::get('tenant/form/{token}', [TenantFormController::class, 'show']);
        Route::post('tenant/form/{token}', [TenantFormController::class, 'submit']);

        // Tenant Authentication
        Route::prefix('tenant')->group(function () {
            Route::post('login', [TenantAuthController::class, 'login']);
            Route::post('register', [TenantAuthController::class, 'register']);
            Route::post('forgot-password', [TenantAuthController::class, 'forgotPassword']);
            Route::post('reset-password', [TenantAuthController::class, 'resetPassword']);
        });

        // Admin Authentication
        Route::prefix('admin')->group(function () {
            // Route::post('login', [AdminAuthController::class, 'login']);
        });
    });
});


Route::group(['middleware' => 'auth:api'], function () {
    //User logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});
