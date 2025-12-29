<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\BedController;
use App\Http\Controllers\Web\Backend\RoomController;
use App\Http\Controllers\Web\Backend\UnitController;
use App\Http\Controllers\Web\Backend\PropertyController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\PropertyTypeController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageController;
use App\Http\Controllers\Web\Backend\CMS\Home\ApartmentController;
use App\Http\Controllers\Web\Backend\CMS\About\AboutPageController;
use App\Http\Controllers\Web\Backend\CMS\Gallery\GalleryController;
use App\Http\Controllers\Web\Backend\CMS\Home\HowItWorksController;
use App\Http\Controllers\Web\Backend\Settings\SocialLinkController;
use App\Http\Controllers\Web\Backend\CMS\Home\EmpAndSponsorController;
use App\Http\Controllers\Web\Backend\CMS\Home\PrimeLocationController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageSliderController;
use App\Http\Controllers\Web\Backend\CMS\Property\PropertyPageController;
use App\Http\Controllers\Web\Backend\CMS\Amenities\AmenitiesPageController;
use App\Http\Controllers\Web\Backend\CMS\Section\CmsSectionController as SectionCmsSectionController;
use App\Http\Controllers\Web\Backend\PropertySection\PropertySectionController;

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

// property type manage
Route::prefix('units')->name('units.')->group(function () {
    Route::get('/list', [UnitController::class, 'index'])->name('list');
    Route::post('/store', [UnitController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [UnitController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [UnitController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [UnitController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [UnitController::class, 'toggleStatus'])->name('toggle.status');
});

Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/list', [RoomController::class, 'index'])->name('list');
    Route::get('/show/{id}', [RoomController::class, 'show'])->name('show');
    Route::get('/create', [RoomController::class, 'create'])->name('create');
    Route::post('/store', [RoomController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [RoomController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [RoomController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RoomController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [RoomController::class, 'toggleStatus'])->name('toggle.status');

    Route::get('/get-units/{propertyId}', [RoomController::class, 'getUnits'])->name('get.units');
});

Route::prefix('beds')->name('beds.')->group(function () {
    Route::get('/list', [BedController::class, 'index'])->name('list');
    Route::post('/store', [BedController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [BedController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [BedController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [BedController::class, 'destroy'])->name('delete');
    Route::post('/bulk-delete', [BedController::class, 'bulkDelete'])->name('bulk-delete');

    Route::get('/toggle-status/{id}', [BedController::class, 'toggleStatus'])->name('toggle.status');
    Route::get('/get-rooms/{unitId}', [BedController::class, 'getRooms'])->name('get.rooms');

});


//Property manage
Route::prefix('property')->name('property.')->group(function () {
    // Dynamic property management (new CMS-style tab system)
    Route::get('/', [PropertySectionController::class, 'index'])->name('list');
    Route::get('/section/{section}', [PropertySectionController::class, 'section'])->name('section');
    
    // Legacy routes for create/edit operations
    Route::get('/list', [PropertyController::class, 'index'])->name('index');
    Route::get('/create', [PropertyController::class, 'create'])->name('create');
    Route::post('/store', [PropertyController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PropertyController::class, 'edit'])->name('edit');
    Route::get('/show/{id}', [PropertyController::class, 'show'])->name('show');
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
    Route::prefix('home/slider')->name('slider.')->group(function () {
        Route::post('/store', [HomePageSliderController::class, 'store'])->name('store');
        Route::put('/{id}', [HomePageSliderController::class, 'update'])->name('update'); // NEW
        Route::post('/{id}/status', [HomePageSliderController::class, 'updateStatus'])->name('status');
        Route::delete('/{id}', [HomePageSliderController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [HomePageSliderController::class, 'updateOrder'])->name('updateOrder');
    });

    // How it works
    Route::prefix('home/how-it-works')->name('home.how-it-works.')->group(function () {
        Route::post('update', [HowItWorksController::class, 'update'])->name('update');
        Route::post('item/store', [HowItWorksController::class, 'storeItem'])->name('store');
        Route::post('item/update', [HowItWorksController::class, 'updateItem'])->name('update.item');
        Route::delete('item/delete', [HowItWorksController::class, 'destroy'])->name('delete');
    });

    // Employee and sponsor
    Route::post('/home/employee-and-sponsor/update', [EmpAndSponsorController::class, 'update'])->name('home.employee.and.sponsor.section.update');

    // Prime Location Section
    Route::post('/home/prime-location/update', [PrimeLocationController::class, 'update'])->name('home.prime.location.section.update');

    // Apartment section update
    Route::post('/home/apartment/update', [ApartmentController::class, 'update'])->name('home.apartment.section.update');

    // Upload gallery image
    Route::post('/gallery/update', [GalleryController::class, 'store'])->name('gallery.section.update');
    Route::delete('/gallery/item/delete/{id}', [GalleryController::class, 'destroy'])->name('gallery.item.delete');

    // Property page
    Route::post('/property/banner/update', [PropertyPageController::class, 'update'])->name('property.banner.update');

    // About section update
    Route::post('/about/breadcrumb/update', [AboutPageController::class, 'update'])->name('about.breadcrumb.update');
    Route::post('/contact/breadcrumb/update', [AboutPageController::class, 'contactUpdate'])->name('contact.breadcrumb.update');

    // Amenities page
    Route::post('/amenities/hero/update', [AmenitiesPageController::class, 'update'])->name('amenities.hero.update');
    Route::prefix('amenities/features')->name('amenities.')->group(function () {
        Route::post('/header/update', [AmenitiesPageController::class, 'headerUpdate'])->name('header.update');
        Route::post('/item/store', [AmenitiesPageController::class, 'storeItem'])->name('item.store');
        Route::post('/item/update', [AmenitiesPageController::class, 'updateItem'])->name('item.update');
        Route::delete('/item/delete', [AmenitiesPageController::class, 'destroy'])->name('item.delete');
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

/**
 * Socials links routes
 */
Route::prefix('social')->name('social.profile.')->group(function () {
    Route::get('/', [SocialLinkController::class, 'index'])->name('index');
    Route::post('/store', [SocialLinkController::class, 'store'])->name('store');
    Route::post('/update/{id}', [SocialLinkController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [SocialLinkController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [SocialLinkController::class, 'status'])->name('status');
});
