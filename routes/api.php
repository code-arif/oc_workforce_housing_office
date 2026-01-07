<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\Tenants\LandingController;
use App\Http\Controllers\Api\Tenants\TenantAuthController;
use App\Http\Controllers\Api\Tenants\TenantFormController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Tenants\PasswordResetController;
use App\Http\Controllers\Api\Tenants\TenantPasswordController;

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
        Route::get('/our-story', [CmsController::class, 'ourStory']); // cms our story page data
        Route::get('/how-it-works', [CmsController::class, 'howItWorks']); // how it works page data
        Route::get('/structure', [CmsController::class, 'structure']); // structure page data
        Route::get('/eligibility', [CmsController::class, 'eligibility']); // eligibility page data
        Route::get('/payment-policy', [CmsController::class, 'paymentPolicy']); // payment policy page data
        Route::get('/tax-policy', [CmsController::class, 'taxPolicy']); // tax policy page data
        Route::get('/ethical-boundaries', [CmsController::class, 'ethicalBoundaries']); // ethical-boundaries page data
        Route::get('/officer-compensation-policy', [CmsController::class, 'officerCompensationPolicy']); // officer compensation policy page data
        Route::get('/archives', [CmsController::class, 'archives']); // archives page data
        Route::get('/contact-us', [CmsController::class, 'contactUs']); // contact-us page data
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
            Route::post('/forgot/reset', [TenantPasswordController::class, 'resetPasswordWithToken']);
        });
    });
});


Route::group(['middleware' => 'auth:api'], function () {
    // Protected Tenant Routes
    Route::prefix('v1/tenant')->group(function () {
        Route::get('/profile', [TenantAuthController::class, 'profile']);
        Route::put('/profile-update', [TenantAuthController::class, 'updateProfile']);
        Route::put('/avatar-update', [TenantAuthController::class, 'updateAvatar']);
        Route::get('/dashboard', [TenantAuthController::class, 'dashboard']);
        Route::post('/logout', [TenantAuthController::class, 'logout']);
        Route::get('/documents', [TenantAuthController::class, 'documents']);
        Route::post('/documents/upload', [TenantAuthController::class, 'uploadDocument']);
    });
});
