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
use App\Http\Controllers\Web\Backend\PropertyTypeController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data'); // working

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
