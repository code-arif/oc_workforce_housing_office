<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\PropertyController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\CMS\HomePageController;
use App\Http\Controllers\Web\Backend\PropertyTypeController;
use App\Http\Controllers\Web\Backend\CMS\CmsSectionController;
use App\Http\Controllers\Web\Backend\CMS\Home\HowItWorksController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\CMS\HomePageSliderController;

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
 * CMS route
 */
Route::prefix('cms')->name('cms.')->group(function () {
    // Main CMS page - defaults to 'hero' section
    Route::get('/', [CmsSectionController::class, 'index'])->name('index'); // working

    // Specific section via AJAX
    Route::get('/section/{section}', [CmsSectionController::class, 'section'])->name('section');

    // Home Hero Section
    Route::post('/home/hero/update', [HomePageController::class, 'update'])->name('home.hero.section.update');

    // Slider Management Routes
    Route::post('/slider/store', [HomePageSliderController::class, 'store'])->name('slider.store');
    Route::post('/slider/{id}/status', [HomePageSliderController::class, 'updateStatus'])->name('slider.status');
    Route::delete('/slider/{id}', [HomePageSliderController::class, 'destroy'])->name('slider.destroy');
    Route::post('/slider/update-order', [HomePageSliderController::class, 'updateOrder'])->name('slider.updateOrder');

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
