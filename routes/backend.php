<?php

use App\Http\Controllers\Api\Backend\Lease\LeaseManageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\BedController;
use App\Http\Controllers\Web\Backend\RoomController;
use App\Http\Controllers\Web\Backend\UnitController;
use App\Http\Controllers\Web\Backend\SeasonController;
use App\Http\Controllers\Web\Backend\AmenityController;
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
use App\Http\Controllers\Web\Backend\Lease\LeaseDocumentController;
use App\Http\Controllers\Web\Backend\Lease\LeaseTemplateController;
use App\Http\Controllers\Web\Backend\Settings\SocialLinkController;
use App\Http\Controllers\Web\Backend\UserManagement\RoleController;
use App\Http\Controllers\Web\Backend\UserManagement\UserController;
use App\Http\Controllers\Web\Backend\CMS\Home\EmpAndSponsorController;
use App\Http\Controllers\Web\Backend\CMS\Home\PrimeLocationController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageSliderController;
use App\Http\Controllers\Web\Backend\CMS\Pricing\PricingPageController;
use App\Http\Controllers\Web\Backend\CMS\Property\PropertyPageController;
use App\Http\Controllers\Web\Backend\UserManagement\PermissionController;
use App\Http\Controllers\Web\Backend\CMS\Amenities\AmenitiesPageController;
use App\Http\Controllers\Web\Backend\CMS\Reservation\ReservationPageController;
use App\Http\Controllers\Web\Backend\PropertySection\PropertySectionController;
use App\Http\Controllers\Web\Backend\CMS\Section\CmsSectionController as SectionCmsSectionController;
use App\Http\Controllers\Web\Backend\Tenant\TenantManageController;

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
    Route::get('/get-data', [PropertyController::class, 'getData'])->name('get.data');
    Route::get('/create', [PropertyController::class, 'create'])->name('create');
    Route::post('/store', [PropertyController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PropertyController::class, 'edit'])->name('edit');
    Route::get('/show/{id}', [PropertyController::class, 'show'])->name('show');
    Route::post('/update/{id}', [PropertyController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [PropertyController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [PropertyController::class, 'toggleStatus'])->name('toggle.status');
});

Route::prefix('seasons')->name('seasons.')->group(function () {
    // Legacy routes for create/edit operations
    Route::get('/list', [SeasonController::class, 'index'])->name('list');
    Route::get('/get-data', [SeasonController::class, 'getData'])->name('get.data');
    Route::post('/store', [SeasonController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [SeasonController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [SeasonController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [SeasonController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [SeasonController::class, 'toggleStatus'])->name('toggle.status');
});

Route::prefix('amenities')->name('amenities.')->group(function () {
    Route::get('/list', [AmenityController::class, 'index'])->name('list');
    Route::post('/store', [AmenityController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [AmenityController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [AmenityController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [AmenityController::class, 'destroy'])->name('delete');

    Route::get('/toggle-status/{id}', [AmenityController::class, 'toggleStatus'])->name('toggle.status');
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

    // Home housing option section
    Route::post('/home/housing-option/update', [HomePageController::class, 'housingOptionupdate'])->name('home.housing.option.section.update');

    // Slider Management Routes
    Route::prefix('home/slider')->name('slider.')->group(function () {
        Route::post('/store', [HomePageSliderController::class, 'store'])->name('store');
        Route::post('/update/{id}', [HomePageSliderController::class, 'update'])->name('update'); // NEW
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
    Route::post('/property/our-offer/update', [PropertyPageController::class, 'updateOurOffer'])->name('property.our-offer.update');

    // Property one
    Route::post('/property/property-one/update', [PropertyPageController::class, 'updatePropertyOne'])->name('property.one.update');
    Route::post('/property/property-two/update', [PropertyPageController::class, 'updatePropertyTwo'])->name('property.two.update');
    Route::post('/property/property-three/update', [PropertyPageController::class, 'updatePropertyThree'])->name('property.three.update');

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

    // Pricing page
    Route::post('/pricing/banner/update', [PricingPageController::class, 'update'])->name('pricing.hero.update');
    Route::prefix('pricing/plans')->name('pricing.')->group(function () {
        Route::get('/', [PricingPageController::class, 'index'])->name('index');
        Route::post('/store', [PricingPageController::class, 'store'])->name('store');
        Route::post('/update', [PricingPageController::class, 'updatePricingItem'])->name('update');
        Route::post('/status', [PricingPageController::class, 'toggleStatus'])->name('status');
        Route::delete('/delete', [PricingPageController::class, 'destroy'])->name('delete');
    });

    // Reservation page
    Route::post('/reservation/hero/update', [ReservationPageController::class, 'update'])->name('reservation.hero.update');
});


/*
|--------------------------------------------------------------------------
| Tenent Management Routes
|--------------------------------------------------------------------------
*/
// Route::group([], function () {
//     Route::get('/tenants', [TenantManageController::class, 'index'])->name('tenants.index');
//     Route::get('/tenants/{id}', [TenantManageController::class, 'show'])->name('tenants.show');
//     Route::get('/details/{id}', [TenantManageController::class, 'getTenantDetails'])->name('tenants.details');
//     Route::delete('/tenants/{id}', [TenantManageController::class, 'destroy'])->name('tenants.destroy');
//     Route::get('/tenants/create', [TenantManageController::class, 'create'])->name('tenants.create');
// });

// Tenant Management Routes
Route::prefix('tenants')->name('tenants.')->group(function () {
    // List and AJAX
    Route::get('/', [TenantManageController::class, 'index'])->name('index');

    // CRUD Operations
    Route::post('/store', [TenantManageController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [TenantManageController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [TenantManageController::class, 'update'])->name('update');
    Route::delete('/{id}', [TenantManageController::class, 'destroy'])->name('destroy');

    // View Details
    Route::get('/{id}', [TenantManageController::class, 'show'])->name('show');
    Route::get('/details/{id}', [TenantManageController::class, 'getTenantDetails'])->name('details');
});

/*
|--------------------------------------------------------------------------
| Lease Management Routes
|--------------------------------------------------------------------------
*/
// Lease Management Routes
Route::prefix('leases')->name('leases.')->group(function () {
    Route::get('/', [LeaseManageController::class, 'index'])->name('index');
    Route::get('/{id}', [LeaseManageController::class, 'show'])->name('show');
    Route::get('/{id}/details', [LeaseManageController::class, 'details'])->name('details');
    Route::post('/store', [LeaseManageController::class, 'store'])->name('store');
    Route::put('/{id}/update', [LeaseManageController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [LeaseManageController::class, 'destroy'])->name('destroy');
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

/**
 * User Management routes
 */
Route::prefix('user-management')->name('user-management.')->group(function () {
    // Users management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/get-data', [UserController::class, 'getData'])->name('get.data');
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Roles management
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/get-data', [RoleController::class, 'getData'])->name('get.data');
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/store', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // Permissions management
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/get-data', [PermissionController::class, 'getData'])->name('get.data');
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/store', [PermissionController::class, 'store'])->name('store');
        Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [PermissionController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
    });
});


// Lease Templates - Document Upload & Management
Route::prefix('lease-templates')->name('lease-templates.')->group(function () {
    Route::get('/', [LeaseTemplateController::class, 'index'])->name('index');
    Route::get('/create', [LeaseTemplateController::class, 'create'])->name('create');
    Route::post('/', [LeaseTemplateController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LeaseTemplateController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LeaseTemplateController::class, 'update'])->name('update');
    Route::delete('/{id}', [LeaseTemplateController::class, 'destroy'])->name('destroy');

    // PDF viewer route
    Route::get('/{id}/pdf', [LeaseTemplateController::class, 'getPdf'])->name('pdf');

    // Generate lease document
    Route::post('/{id}/generate-lease', [LeaseTemplateController::class, 'generateLease'])->name('generate-lease');

    // Additional routes for template management
    Route::get('/{id}/preview', [LeaseTemplateController::class, 'preview'])->name('preview');
    Route::post('/{id}/toggle-status', [LeaseTemplateController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/{id}/duplicate', [LeaseTemplateController::class, 'duplicate'])->name('duplicate');
    Route::get('/{id}/export', [LeaseTemplateController::class, 'export'])->name('export');
});

// Lease Document Routes
Route::prefix('lease-documents')->name('lease-documents.')->group(function () {
    Route::get('/0', [LeaseDocumentController::class, 'index'])->name('index');
    Route::get('/0/create', [LeaseDocumentController::class, 'create'])->name('create');
    Route::post('/0', [LeaseDocumentController::class, 'store'])->name('store');
    Route::get('/0/{id}', [LeaseDocumentController::class, 'show'])->name('show');
    Route::get('/0/{id}/edit', [LeaseDocumentController::class, 'edit'])->name('edit');
    Route::put('/0/{id}', [LeaseDocumentController::class, 'update'])->name('update');
    Route::post('/0/{id}/sign-admin', [LeaseDocumentController::class, 'signAdmin'])->name('sign-admin');
    Route::post('/0/{id}/sign-tenant', [LeaseDocumentController::class, 'signTenant'])->name('sign-tenant');
    Route::get('/0/{id}/download-pdf', [LeaseDocumentController::class, 'downloadPdf'])->name('download-pdf');
});
