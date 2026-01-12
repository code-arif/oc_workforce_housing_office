<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\Tenants\LandingController;
use App\Http\Controllers\Api\Tenants\TenantAuthController;
use App\Http\Controllers\Api\Tenants\TenantFormController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Tenants\MaintananceController;
use App\Http\Controllers\Api\Tenants\PasswordResetController;
use App\Http\Controllers\Api\Tenants\TenantDashboardController;
use App\Http\Controllers\Api\Tenants\TenantPasswordController;
use App\Http\Controllers\Api\Tenants\TenantProfileController;

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
    | Cms Routes
    |--------------------------------------------------------------------------
    */
    // cms route gorup
    Route::group(['prefix' => 'cms'], function () {
        Route::get('/home', [CmsController::class, 'home']); // cms home page data
        Route::get('/properties', [CmsController::class, 'properties']); // cms properties page data
        Route::get('/about-us', [CmsController::class, 'aboutUs']); // cms about us page data
        Route::get('/amenities', [CmsController::class, 'amenities']); // cms amenities page data
        Route::get('/pricing', [CmsController::class, 'pricing']); // cms pricing page data
    });


    /*
    |--------------------------------------------------------------------------
    | Tenent Management Routes
    |--------------------------------------------------------------------------
    */
    // Public Routes
    Route::prefix('v1')->group(function () {

        // Landing - Tenant Email Submission
        Route::post('/tenant/apply', [LandingController::class, 'submitEmail']); // done

        // Admin Actions
        Route::prefix('admin')->group(function () {
            Route::post('/tenant/proceed/email', [LandingController::class, 'adminEmailProceed']); // only for developemnt purpose
            Route::post('/tenant/proceed/application', [LandingController::class, 'adminApplicationProceed']); // only for developemnt purpose
        });

        // Tenant Form (Token-based)
        Route::prefix('tenant/form')->group(function () {
            Route::get('/{token}', [TenantFormController::class, 'show']);
            Route::post('/{token}', [TenantFormController::class, 'submit']); // done
        });

        // Tenant Authentication
        Route::prefix('tenant')->group(function () {
            Route::post('/login', [TenantAuthController::class, 'login']);
        });


        // Tenant Password Management
        Route::prefix('tenant/password')->group(function () {
            // First time password setup (after approval)
            Route::post('/setup', [TenantPasswordController::class, 'setPasswordAfterApproval']); // done

            // Forgot password flow (OTP-based)
            Route::post('/forgot/send-otp', [TenantPasswordController::class, 'sendForgotPasswordOTP']); // done
            Route::post('/forgot/verify-otp', [TenantPasswordController::class, 'verifyOTP']); // done
            Route::post('/reset', [TenantPasswordController::class, 'resetPasswordWithToken']); // done
        });


        // Maintance routes
        Route::prefix('tenant/maintanance')->group(function () {
            Route::get('/list', [MaintananceController::class, 'index']);
            Route::post('/store', [MaintananceController::class, 'store']);
            Route::get('/edit/{maintananceId}', [MaintananceController::class, 'edit']);
            Route::post('/update/{maintananceId}', [MaintananceController::class, 'update']);
            Route::delete('/delete/{maintananceId}', [MaintananceController::class, 'destroy']);
        });
    });
});


Route::group(['middleware' => 'auth:api'], function () {
    // Protected Tenant Routes
    Route::prefix('v1/tenant')->group(function () {
        Route::get('/profile', [TenantProfileController::class, 'profile']); // done
        Route::put('/update-profile', [TenantProfileController::class, 'updateProfile']); // done
        Route::post('/update-avatar', [TenantProfileController::class, 'updateAvatar']); // done

        Route::post('/logout', [TenantAuthController::class, 'logout']); // done


        // Tenant dashbaord routes
        Route::get('/dashboard', [TenantDashboardController::class, 'dashboard']); // done
        Route::get('/documents', [TenantDashboardController::class, 'documents']); // done
        Route::post('/documents/upload', [TenantDashboardController::class, 'uploadDocument']);


        // Maintance routes
        Route::prefix('/maintanance')->group(function () {
            Route::get('/list', [MaintananceController::class, 'index']);
            Route::post('/store', [MaintananceController::class, 'store']); // done
            Route::get('/edit/{maintananceId}', [MaintananceController::class, 'edit']); // done
            Route::post('/update/{maintananceId}', [MaintananceController::class, 'update']); // done
            Route::delete('/delete/{maintananceId}', [MaintananceController::class, 'destroy']);
        });
    });
});
