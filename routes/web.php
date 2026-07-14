<?php
use App\Http\Controllers\Api\Tenants\TenantPaymentController;
use Illuminate\Support\Facades\Route;

// Handle webhook
Route::post('/stripe/webhook', [TenantPaymentController::class, 'handleWebhook']);

require __DIR__ . '/auth.php';
