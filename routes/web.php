<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Calendar\TrashController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\Calendar\CalendarCrudController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Calendar\BiDirectionalSyncController;
use App\Http\Controllers\Calendar\EventManageGoogleController;
use App\Http\Controllers\Calendar\GetEventFromGoogleController;
use App\Http\Controllers\Calendar\SyncEventFromGoogleController;
use App\Http\Controllers\Calendar\SyncMultipleGoogleCalendarsController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/run-migrate', function () {
    try {
        $output = Artisan::call('migrate:fresh');
        return response()->json([
            'message' => 'Migrations executed.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/run-migrate-fresh', function () {
    try {
        $output = Artisan::call('migrate:fresh', ['--seed' => true]);
        return response()->json([
            'message' => 'Migrations executed.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Run composer update
Route::get('/run-composer-update', function () {
    $output = shell_exec('composer update 2>&1');
    return response()->json([
        'message' => 'Composer update command executed.',
        'output' => nl2br($output)
    ]);
});
// Run optimize:clear
Route::get('/run-optimize-clear', function () {
    $output = Artisan::call('optimize:clear');
    return response()->json([
        'message' => 'Optimize clear command executed.',
        'output' => nl2br($output)
    ]);
});
// Run db:seed
Route::get('/run-db-seed', function () {
    $output = Artisan::call('db:seed', ['--force' => true]);
    return response()->json([
        'message' => 'Database seeding executed.',
        'output' => nl2br($output)
    ]);
});
// Run cache:clear
Route::get('/run-cache-clear', function () {
    $output = Artisan::call('cache:clear');
    return response()->json([
        'message' => 'Cache cleared.',
        'output' => nl2br($output)
    ]);
});
// Run queue:restart
Route::get('/run-queue-restart', function () {
    $output = Artisan::call('queue:restart');
    return response()->json([
        'message' => 'Queue workers restarted.',
        'output' => nl2br($output)
    ]);
});

// Create storage symbolic link
Route::get('/run-storage-link', function () {
    try {
        $output = Artisan::call('storage:link');
        return response()->json([
            'message' => 'Storage symbolic link created.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while creating storage symbolic link.',
            'error' => $e->getMessage(),
        ], 500);
    }
});


// teacher email verification
Route::get('/verify-email/{token}', [AuthenticationController::class, 'verifyEmail'])->name('verify.email');



Route::middleware(['auth', 'admin'])->group(function () {
    // Calendar Routes
    Route::get('/calendar', [GoogleCalendarController::class, 'index'])->name('calendar.index'); // working
    Route::get('/calendar/events', [GetEventFromGoogleController::class, 'getEvents'])->name('calendar.events'); // working

    // Google OAuth Routes
    Route::get('/google/redirect', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.redirect'); // working
    Route::get('/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback'])->name('google.callback'); // working
    Route::get('/google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.disconnect'); // working
    Route::post('/google/sync', [SyncEventFromGoogleController::class, 'syncFromGoogle'])->name('google.sync'); // working

    // Work CRUD Routes (Modal based)
    Route::post('/calendar/store', [EventManageGoogleController::class, 'store'])->name('calendar.store'); // working
    Route::get('/calendar/{work}', [EventManageGoogleController::class, 'show'])->name('calendar.show'); // working
    Route::post('/calendar/{work}', [EventManageGoogleController::class, 'update'])->name('calendar.update'); // working
    Route::delete('/calendar/{work}', [EventManageGoogleController::class, 'destroy'])->name('calendar.destroy'); // working


    Route::get('/calendars/list', [CalendarCrudController::class, 'index'])->name('calendars.list');
    Route::post('/calendars/create', [CalendarCrudController::class, 'store'])->name('calendars.create');
    Route::put('/calendars/{calendar}', [CalendarCrudController::class, 'update'])->name('calendars.update');
    Route::post('/calendars/{calendar}/toggle', [CalendarCrudController::class, 'toggleVisibility'])->name('calendars.toggle');
    Route::delete('/calendars/{calendar}', [CalendarCrudController::class, 'destroy'])->name('calendars.destroy');

    // Multi-calendar sync
    Route::post('/google/sync-all', [SyncMultipleGoogleCalendarsController::class, 'syncAll'])->name('google.sync.all');
    Route::post('/google/full-sync', [BiDirectionalSyncController::class, 'fullSync'])->name('google.full.sync');

    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/{id}/force-delete', [TrashController::class, 'forceDelete'])->name('trash.forceDelete');
    Route::delete('/trash/empty', [TrashController::class, 'emptyTrash'])->name('trash.empty');
});


require __DIR__ . '/auth.php';
