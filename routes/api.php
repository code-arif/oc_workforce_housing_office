<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CMS\CmsController;
use App\Http\Controllers\Api\Tenants\LandingController;
use App\Http\Controllers\Api\Tenants\TenantAuthController;
use App\Http\Controllers\Api\Tenants\TenantFormController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Tenants\MaintananceController;
use App\Http\Controllers\Api\Tenants\PasswordResetController;
use App\Http\Controllers\Api\Tenants\TenantPaymentController;
use App\Http\Controllers\Api\Tenants\TenantProfileController;
use App\Http\Controllers\Api\Tenants\TenantPasswordController;
use App\Http\Controllers\Api\Tenants\TenantDashboardController;
use App\Http\Controllers\Api\Tenants\TenantLeaseSignController;

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
        Route::get('/reservation', [CmsController::class, 'reservation']); // cms reservation page data
    });


    /*
    |--------------------------------------------------------------------------
    | Tenent Routes
    |--------------------------------------------------------------------------
    */
    // Public Routes
    Route::prefix('v1')->group(function () {

        // Landing - Tenant Email Submission
        Route::post('/tenant/apply', [LandingController::class, 'submitEmail']); // done

        // Admin Actions
        Route::prefix('admin')->group(function () {
            Route::post('/tenant/proceed/application', [LandingController::class, 'adminApplicationProceed']); // only for developemnt purpose
        });

        // Tenant Form (Token-based)
        Route::prefix('tenant/form')->group(function () {
            Route::post('/', [TenantFormController::class, 'submit']); // done
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

        // Lease Routes
        Route::prefix('leases')->name('leases.')->group(function () {
            Route::get('/', [TenantDashboardController::class, 'leases']); // done
            Route::get('/{leaseId}', [TenantDashboardController::class, 'leaseDetails']); // done
        });

        // Invoice Routes
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [TenantDashboardController::class, 'invoices']); // done
            Route::get('/{invoiceId}', [TenantDashboardController::class, 'invoiceDetails']); // done
        });

        // Payment History & Transactions
        Route::get('/payments', [TenantDashboardController::class, 'paymentHistory']); // ISSUE
        Route::get('/transactions', [TenantDashboardController::class, 'transactions']); // ISSUE


        // Lease Signing Routes
        Route::prefix('lease-signing')->name('lease.signing.')->group(function () {
            Route::get('/{leaseId}/document', [TenantLeaseSignController::class, 'getLeaseDocument']); // done
            Route::post('/{leaseId}/sign', [TenantLeaseSignController::class, 'signLease']); // done
            Route::get('/{leaseId}/eligibility', [TenantLeaseSignController::class, 'checkSigningEligibility']); // ISSUE
            Route::get('/{leaseId}/preview', [TenantLeaseSignController::class, 'previewDocument']); // done
        });

        // Payment Routes (Stripe)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/invoice/{invoiceId}/details', [TenantPaymentController::class, 'getPaymentDetails']); // done
            Route::post('/checkout/create', [TenantPaymentController::class, 'createCheckoutSession']); // ISSUE
            Route::post('/verify', [TenantPaymentController::class, 'verifyPayment']); // done - only for development stage
            Route::post('/calculate', [TenantPaymentController::class, 'calculatePayment']); 
            Route::get('/history', [TenantPaymentController::class, 'paymentHistory']);
        });

        // Maintance routes
        Route::prefix('/maintanance')->group(function () {
            Route::get('/list', [MaintananceController::class, 'index']); // done
            Route::post('/store', [MaintananceController::class, 'store']); // done
            Route::get('/edit/{maintananceId}', [MaintananceController::class, 'edit']); // done
            Route::post('/update/{maintananceId}', [MaintananceController::class, 'update']); // done
            Route::delete('/delete/{maintananceId}', [MaintananceController::class, 'destroy']);
        });
    });
});
