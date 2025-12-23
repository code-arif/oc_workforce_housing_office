<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TrackingController;
use App\Http\Controllers\Web\Backend\MapController;
use App\Http\Controllers\Web\Backend\CalendarController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\WorkScheduleRequest;
use App\Http\Controllers\Web\Backend\TeamManageController;
use App\Http\Controllers\Web\Backend\WorkManageController;
use App\Http\Controllers\Web\Backend\EmployeeAssignController;
use App\Http\Controllers\Web\Backend\EmployeeManageController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data'); // working

    // employee manage
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/list', [EmployeeManageController::class, 'index'])->name('list');
        Route::post('/store', [EmployeeManageController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [EmployeeManageController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [EmployeeManageController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [EmployeeManageController::class, 'delete'])->name('delete');
    });

    // team manage
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/list', [TeamManageController::class, 'index'])->name('list');
        Route::post('/store', [TeamManageController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [TeamManageController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [TeamManageController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [TeamManageController::class, 'delete'])->name('delete');

        Route::get('/teams', [TeamManageController::class, 'teamList'])->name('list.work');

        // team work view in map with polyline
        Route::get('/map/team/{id}/works', [TeamManageController::class, 'mapWorkList'])->name('work.map.list');

        // NEW ROUTES FOR LEADER MANAGEMENT
        Route::get('/leader/members/{id}', [TeamManageController::class, 'getTeamMembers'])->name('leader.members');
        Route::post('/leader/update', [TeamManageController::class, 'updateLeader'])->name('leader.update');
    });

    // assing employee into team manage
    Route::prefix('assign-emplyee')->name('assing.employee.')->group(function () {
        Route::post('/store', [EmployeeAssignController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [EmployeeAssignController::class, 'edit'])->name('edit');
    });

    // work manage
    Route::prefix('work')->name('work.')->group(function () {
        Route::get('/list', [WorkManageController::class, 'index'])->name('list');
        Route::post('/store', [WorkManageController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [WorkManageController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [WorkManageController::class, 'update'])->name('update');
        Route::delete('/delete/{work}', [WorkManageController::class, 'destroy'])->name('delete');
        Route::post('/complation/{id}', [WorkManageController::class, 'complation'])->name('complation.status');

        // category
        Route::get('/category', [WorkManageController::class, 'getCategory'])->name('categroy');

        // work reschedule request
        Route::get('/work/reschedule/{id}', [WorkManageController::class, 'rescheduleShow'])->name('reschedule.show');
        Route::get('/reschedule-request/edit/{id}', [WorkManageController::class, 'rescheduleEdit'])->name('reschedule.edit');
        Route::post('/reschedule-request/update/{id}', [WorkManageController::class, 'rescheduleUpdate'])->name('reschedule.update');
    });

    // work reschedule request
    Route::get('reschedule-request', [WorkScheduleRequest::class, 'index'])->name('reschedule.work.list');
    Route::get('reschedule-request/edit/{id}', [WorkScheduleRequest::class, 'edit'])->name('reschedule.work.edit');
    Route::post('reschedule-request/update/{id}', [WorkScheduleRequest::class, 'update'])->name('reschedule.work.update');

    // work map view
    Route::get('/global-map', [MapController::class, 'globalMap'])->name('map.global');
    Route::get('/filter-works/{teamId}', [MapController::class, 'filterWorksByTeam'])->name('works.filter');
    Route::get('/works/search-teams', [MapController::class, 'searchTeams'])->name('works.searchTeams');


    // Admin routes
    Route::prefix('tracking')->name('admin.')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('tracking.index');
        Route::get('/locations', [TrackingController::class, 'getLocations'])->name('tracking.locations');
        Route::get('/teams', [TrackingController::class, 'getActiveTeams'])->name('tracking.teams');
        Route::get('/team/{teamId}/history', [TrackingController::class, 'getTeamHistory'])->name('tracking.team.history');
        Route::get('/team/{teamId}/route', [TrackingController::class, 'getTeamRoute'])->name('tracking.team.route');
    });
});



//! Route for Profile Settings
Route::controller(ProfileController::class)->group(function () {
    Route::get('setting/profile', 'index')->name('setting.profile.index');
    Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
    Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
    Route::post('setting/profile/update/Picture', 'UpdateProfilePicture')->name('update.profile.picture');
});



//! Route for Stripe Settings
Route::controller(SettingController::class)->group(function () {
    Route::get('setting/general', 'index')->name('setting.general.index');
    Route::patch('setting/general', 'update')->name('setting.general.update');
});
