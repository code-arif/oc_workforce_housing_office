<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\BedController;
use App\Http\Controllers\Web\Backend\RoomController;
use App\Http\Controllers\Web\Backend\PropertyController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\PropertyTypeController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageController;
use App\Http\Controllers\Web\Backend\CMS\Home\HowItWorksController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageSliderController;
use App\Http\Controllers\Web\Backend\CMS\Section\CmsSectionController as SectionCmsSectionController;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data'); // working

});

// property type manage
Route::prefix('property-type')->name('property-type.')->group(function () {
    Route::get('/list', [PropertyTypeController::class, 'index'])->name('list');
    Route::post('/store', [PropertyTypeController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PropertyTypeController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [PropertyTypeController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [PropertyTypeController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [PropertyTypeController::class, 'toggleStatus'])->name('toggle.status');
});

Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/list', [RoomController::class, 'index'])->name('list');
    Route::get('/create', [RoomController::class, 'create'])->name('create');
    Route::post('/store', [RoomController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [RoomController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [RoomController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RoomController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [PropertyController::class, 'toggleStatus'])->name('toggle.status');
});

Route::prefix('beds')->name('beds.')->group(function () {
    Route::get('/list', [BedController::class, 'index'])->name('list');
    Route::post('/store', [BedController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [BedController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [BedController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [BedController::class, 'destroy'])->name('delete');
    Route::post('/bulk-delete', [BedController::class, 'bulkDelete'])->name('bulk-delete');

    Route::get('/toggle-status/{id}', [BedController::class, 'toggleStatus'])->name('toggle.status');
});


//Property manage
Route::prefix('property')->name('property.')->group(function () {
    Route::get('/list', [PropertyController::class, 'index'])->name('list');
    Route::post('/store', [PropertyController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PropertyController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [PropertyController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [PropertyController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [PropertyController::class, 'toggleStatus'])->name('toggle.status');
});

/**
 * Cms routes
 */
Route::prefix('cms')->name('cms.')->group(function () {
    // Main CMS page - defaults to 'hero' section
    Route::get('/', [SectionCmsSectionController::class, 'index'])->name('index'); // working

    // Specific section via AJAX
    Route::get('/section/{section}', [SectionCmsSectionController::class, 'section'])->name('section');

    // Home Hero Section
    Route::post('/home/hero/update', [HomePageController::class, 'update'])->name('home.hero.section.update');

    // Slider Management Routes
    Route::prefix('home/slider')->name('home.sider')->group(function () {
        Route::post('/store', [HomePageSliderController::class, 'store'])->name('store');
        Route::post('/{id}/status', [HomePageSliderController::class, 'updateStatus'])->name('status');
        Route::delete('/delete/{id}', [HomePageSliderController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [HomePageSliderController::class, 'updateOrder'])->name('update.order');
    });

    // How it works
    Route::prefix('home/how-it-works')->name('home.how-it-works.')->group(function () {
        Route::post('update', [HowItWorksController::class, 'update'])->name('update');
        Route::get('item', [HowItWorksController::class, 'index'])->name('index');
        Route::post('item/store', [HowItWorksController::class, 'store'])->name('store');
        Route::post('item/update', [HowItWorksController::class, 'updateItem'])->name('update.item');
        Route::delete('item/delete', [HowItWorksController::class, 'delete'])->name('delete');
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
