<?php

use App\Http\Controllers\Web\Backend\AmenityController;
use App\Http\Controllers\Web\Backend\BedController;
use App\Http\Controllers\Web\Backend\CMS\About\AboutPageController;
use App\Http\Controllers\Web\Backend\CMS\Amenities\AmenitiesPageController;
use App\Http\Controllers\Web\Backend\CMS\Gallery\GalleryController;
use App\Http\Controllers\Web\Backend\CMS\Home\ApartmentController;
use App\Http\Controllers\Web\Backend\CMS\Home\EmpAndSponsorController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageHousingOptionController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomePageSliderController;
use App\Http\Controllers\Web\Backend\CMS\Home\HomeVideoController;
use App\Http\Controllers\Web\Backend\CMS\Home\HowItWorksController;
use App\Http\Controllers\Web\Backend\CMS\Home\PrimeLocationController;
use App\Http\Controllers\Web\Backend\CMS\Pricing\PricingPageController;
use App\Http\Controllers\Web\Backend\CMS\Property\PropertyPageController;
use App\Http\Controllers\Web\Backend\CMS\Reservation\ReservationPageController;
use App\Http\Controllers\Web\Backend\CMS\Section\CmsSectionController as SectionCmsSectionController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\Income\IncomeController;
use App\Http\Controllers\Web\Backend\ItemController;
use App\Http\Controllers\Web\Backend\Lease\LeaseController;
use App\Http\Controllers\Web\Backend\Lease\LeaseDocumentController;
use App\Http\Controllers\Web\Backend\Lease\LeaseTemplateController;
use App\Http\Controllers\Web\Backend\Messaging\MessagingController;
use App\Http\Controllers\Web\Backend\PropertyController;
use App\Http\Controllers\Web\Backend\PropertySection\PropertySectionController;
use App\Http\Controllers\Web\Backend\PropertyTypeController;
use App\Http\Controllers\Web\Backend\Reports\PropertyReportController;
use App\Http\Controllers\Web\Backend\Reports\RentCollectionReportController;
use App\Http\Controllers\Web\Backend\Reports\RentReportController;
use App\Http\Controllers\Web\Backend\Reports\TenantReportController;
use App\Http\Controllers\Web\Backend\RoomController;
use App\Http\Controllers\Web\Backend\SeasonController;
use App\Http\Controllers\Web\Backend\Settings\MailTemplateController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\SocialLinkController;
use App\Http\Controllers\Web\Backend\Stripe\StripeConnectController;
use App\Http\Controllers\Web\Backend\Tenant\ApplicationController;
use App\Http\Controllers\Web\Backend\Tenant\MaintananceController;
use App\Http\Controllers\Web\Backend\Tenant\PaymentManageController;
use App\Http\Controllers\Web\Backend\Tenant\TenantManageController;
use App\Http\Controllers\Web\Backend\UnitController;
use App\Http\Controllers\Web\Backend\UserManagement\PermissionController;
use App\Http\Controllers\Web\Backend\UserManagement\RoleController;
use App\Http\Controllers\Web\Backend\UserManagement\UserController;
use App\Http\Controllers\Web\Backend\SystemMonitorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data'); // DONE
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
    Route::get('/get-amenities', [BedController::class, 'getAmenities'])->name('get.amenities');

    Route::get('/get-beds/{roomId}', [BedController::class, 'getBeds'])->name('get.beds');
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

    // Trash management routes
    Route::get('/trash/data', [PropertyController::class, 'getTrashData'])->name('trash.data');
    Route::post('/{id}/restore', [PropertyController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [PropertyController::class, 'forceDelete'])->name('force-delete');

    // Stripe Connect routes
    Route::prefix('{id}/stripe')->name('stripe.connect.')->group(function () {
        Route::get('/connect', [StripeConnectController::class, 'connect'])->name('connect');
        Route::get('/return', [StripeConnectController::class, 'handleReturn'])->name('return');
        Route::get('/refresh', [StripeConnectController::class, 'handleRefresh'])->name('refresh');
        Route::get('/sync', [StripeConnectController::class, 'syncStatus'])->name('sync');
        Route::get('/dashboard', [StripeConnectController::class, 'dashboard'])->name('dashboard');
        Route::delete('/disconnect', [StripeConnectController::class, 'disconnect'])->name('disconnect');
    });
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

    // Home who we are section
    Route::post('/home/who-we-are/update', [HomePageController::class, 'whoWeAreUpdate'])->name('home.who.we.are.section.update');

    // Slider Management Routes
    Route::prefix('home/slider')->name('slider.')->group(function () {
        Route::post('/store', [HomePageSliderController::class, 'store'])->name('store');
        Route::post('/update/{id}', [HomePageSliderController::class, 'update'])->name('update'); // NEW
        Route::post('/{id}/status', [HomePageSliderController::class, 'updateStatus'])->name('status');
        Route::delete('/{id}', [HomePageSliderController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [HomePageSliderController::class, 'updateOrder'])->name('updateOrder');
    });

    Route::prefix('home/housing-option')->name('housing.option.')->group(function () {
        // Accordion CRUD
        Route::post('/accordion/store', [HomePageHousingOptionController::class, 'storeAccordion'])->name('accordion.store');
        Route::post('/accordion/update/{id}', [HomePageHousingOptionController::class, 'updateAccordion'])->name('accordion.update');
        Route::delete('/accordion/{id}', [HomePageHousingOptionController::class, 'destroyAccordion'])->name('accordion.destroy');
        Route::post('/accordion/update-order', [HomePageHousingOptionController::class, 'updateOrder'])->name('accordion.updateOrder');
    });

    // Video Section Routes
    Route::prefix('home/video')->name('video.')->group(function () {
        Route::post('/store', [HomeVideoController::class, 'store'])->name('store');
        Route::post('/update/{id}', [HomeVideoController::class, 'update'])->name('update');
        Route::post('/{id}/status', [HomeVideoController::class, 'updateStatus'])->name('status');
        Route::delete('/{id}', [HomeVideoController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [HomeVideoController::class, 'updateOrder'])->name('updateOrder');
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

    // Upload gallery page
    Route::post('/gallery/update', [GalleryController::class, 'store'])->name('gallery.section.update');
    Route::delete('/gallery/item/delete/{id}', [GalleryController::class, 'destroy'])->name('gallery.item.delete');

    // Property page
    Route::post('/property/banner-one/update', [PropertyPageController::class, 'updatePropertyBannerOne'])->name('property.banner-one.update');
    Route::post('/property/banner-two/update', [PropertyPageController::class, 'updatePropertyBannerTwo'])->name('property.banner-two.update');
    Route::post('/property/banner-three/update', [PropertyPageController::class, 'updatePropertyBannerThree'])->name('property.banner-three.update');
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
Route::group([], function () {
    Route::get('/tenants', [TenantManageController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/data', [TenantManageController::class, 'getData'])->name('tenants.get.data');
    Route::get('/tenants/edit/{id}', [TenantManageController::class, 'edit'])->name('tenants.edit');
    Route::get('/tenants/{id}', [TenantManageController::class, 'show'])->name('tenants.show');
    Route::get('/details/{id}', [TenantManageController::class, 'getTenantDetails'])->name('tenants.details');
    Route::delete('/tenants/{id}', [TenantManageController::class, 'destroy'])->name('tenants.destroy');
    Route::get('/tenants/create', [TenantManageController::class, 'create'])->name('tenants.create');
    Route::post('/tenants/update/{id}', [TenantManageController::class, 'update'])->name('tenants.update');
    Route::post('/tenants/store', [TenantManageController::class, 'store'])->name('tenants.store');

    Route::get('/tenants/export/excel', [TenantManageController::class, 'export'])->name('tenants.export'); // DONE: Tenant Export in excel
    Route::post('/tenants/{id}/approve', [TenantManageController::class, 'approveStatus'])->name('tenants.approve'); // DONE: Tenant approval

    // Tenant API routes for lease creation
    Route::get('/tenants/0/active', [TenantManageController::class, 'getActiveTenants'])->name('tenants.active');
    Route::post('/tenants/quick-create', [TenantManageController::class, 'quickCreate'])->name('tenants.quick-create');

    // Payment & Transaction History Routes
    Route::get('/tenants/{id}/payments/history', [PaymentManageController::class, 'getPaymentHistory'])
        ->name('tenants.payments.history');

    Route::get('/tenants/{id}/transactions/history', [PaymentManageController::class, 'getTransactionHistory'])
        ->name('tenants.transactions.history');

    Route::get('/tenants/payments/{id}/details', [PaymentManageController::class, 'getPaymentDetails'])
        ->name('tenants.payments.details');

    Route::post('/tenants/payments/{id}/review', [PaymentManageController::class, 'updatePaymentReview'])
        ->name('tenants.payments.review');

    Route::get('/tenants/{id}/payments/export', [PaymentManageController::class, 'exportPaymentHistory'])
        ->name('tenants.payments.export');
});


Route::group([], function () {
    // View applications page with tabs
    Route::get('/applications', [ApplicationController::class, 'index'])->name('tenants.applications.index');

    // Get DataTables data (supports type: single | reservation)
    Route::get('/applications/data', [ApplicationController::class, 'getData'])->name('tenants.applications.get.data');
    Route::get('/applications/reservation/data', [ApplicationController::class, 'getReservationData'])->name('tenants.applications.get.reservation.data');
    Route::get('/applications/invitations', [ApplicationController::class, 'getInvitationData'])->name('tenants.applications.invitations');

    // View specific application details
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('tenants.applications.show');

    // Reservation request detail & status update
    Route::get('/reservation-requests/{id}', [ApplicationController::class, 'showReservation'])->name('tenants.reservation.show');
    Route::post('/reservation-requests/{id}/status', [ApplicationController::class, 'updateReservationStatus'])->name('tenants.reservation.update.status');

    // Approve single email application
    Route::post('/applications/{id}/approve-single', [ApplicationController::class, 'approveSingleEmail'])->name('tenants.applications.approve.single');

    // Approve reservation application
    // Route::post('/applications/{id}/approve-reservation', [ApplicationController::class, 'approveReservation'])->name('tenants.applications.approve.reservation');

    // Reject any application
    Route::post('/applications/{id}/reject', [ApplicationController::class, 'reject'])->name('tenants.applications.reject');

    // Send Invitation
    Route::post('/applications/send-invitation', [ApplicationController::class, 'sendInvitation'])->name('tenants.applications.invite');
});


/*
|--------------------------------------------------------------------------
| Lease Management Routes
|--------------------------------------------------------------------------
*/
// Lease Management Routes
Route::prefix('leases')->name('leases.')->group(function () {
    Route::get('/', [LeaseController::class, 'index'])->name('index');
    Route::get('/get-data', [LeaseController::class, 'getData'])->name('get.data');
    Route::get('/create', [LeaseController::class, 'create'])->name('create');
    Route::get('/{id}', [LeaseController::class, 'show'])->name('show');
    Route::get('/{id}/details', [LeaseController::class, 'details'])->name('details');
    Route::post('/store', [LeaseController::class, 'store'])->name('store');
    Route::put('/{id}/update', [LeaseController::class, 'update'])->name('update');
    Route::delete('/{id}/delete', [LeaseController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/collect-deposit', [LeaseController::class, 'collectDeposit'])->name('collect.deposit');
    Route::post('/{id}/resend-for-signature', [LeaseController::class, 'resendForSignature'])->name('resend.signature');

    // Manual lease close routes
    Route::get('/{id}/close-data', [LeaseController::class, 'getCloseData'])->name('close.data');
    Route::post('/{id}/close', [LeaseController::class, 'closeLease'])->name('close');

    // Change bed assignment routes
    Route::get('/{id}/change-bed-data', [LeaseController::class, 'getChangeBedData'])->name('change.bed.data');
    Route::post('/{id}/change-bed', [LeaseController::class, 'changeBed'])->name('change.bed');

    // Initial bed assignment routes (for leases created without bed)
    Route::get('/{id}/assign-bed-data', [LeaseController::class, 'getAssignBedData'])->name('assign.bed.data');
    Route::post('/{id}/assign-bed', [LeaseController::class, 'assignBed'])->name('assign.bed');
    Route::get('/tenant/{tenantId}/pending-bed-assignments', [LeaseController::class, 'getPendingBedAssignments'])->name('pending.bed.assignments');

    Route::get('/property/{id}/beds', [LeaseController::class, 'getBedsByProperty'])->name('property.beds');
});

// Invoice Routes
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/{id}', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'show'])->name('show');
    Route::get('/{id}/download-pdf', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'downloadPdf'])->name('download.pdf');
    Route::put('/{id}', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'update'])->name('update');
    Route::post('/{id}/payments', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'storePayment'])->name('payments.store');
    Route::get('/{id}/payments', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'getPayments'])->name('payments.index');
    Route::post('/{id}/mark-paid', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'markPaid'])->name('mark.paid');
    Route::post('/{id}/cancel', [\App\Http\Controllers\Web\Backend\Lease\InvoiceController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| Income Routes
|--------------------------------------------------------------------------
*/
// Invoice Routes
Route::prefix('incomes')->name('invoices.')->group(function () {
    Route::get('/', [IncomeController::class, 'index'])->name('index');
    Route::get('/get-data', [IncomeController::class, 'getData'])->name('get.data');
    Route::get('/create', [IncomeController::class, 'create'])->name('create');
    Route::post('/store', [IncomeController::class, 'store'])->name('store');
    // Route::get('/{id}', [IncomeController::class, 'show'])->name('show');
    Route::get('/tenant/{tenantId}/info', [IncomeController::class, 'getTenantInfo'])->name('tenant.info');
});


/*
|--------------------------------------------------------------------------
| Maintanance Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/maintanance')->name('maintanance.')->group(function () {
    Route::get('/list', [MaintananceController::class, 'index'])->name('index'); // done
    Route::get('/get-data', [MaintananceController::class, 'getData'])->name('getData'); // done
    Route::get('/create', [MaintananceController::class, 'create'])->name('create'); // done
    Route::post('/store', [MaintananceController::class, 'store'])->name('store'); // done
    Route::get('/edit/{maintananceId}', [MaintananceController::class, 'edit'])->name('edit'); // done
    Route::post('/update/{maintananceId}', [MaintananceController::class, 'update'])->name('update'); // done
    Route::delete('/delete/{maintananceId}', [MaintananceController::class, 'destroy'])->name('delete'); //done
    Route::get('/details/{id}', [MaintananceController::class, 'show'])->name('show'); // done
    Route::get('/maintanance/{id}/details', [MaintananceController::class, 'details'])->name('details'); // done

    Route::post('/{id}/mark-resolved', [MaintananceController::class, 'markAsResolved'])->name('markResolved');
    Route::post('/{id}/update-status', [MaintananceController::class, 'updateStatus'])->name('updateStatus');

    Route::get('/tenant-lease-properties', [MaintananceController::class, 'getTenantLeaseProperties'])->name('tenant.lease.properties');
    Route::get('property-units', [MaintananceController::class, 'getPropertyUnits'])->name('property.units');
    Route::get('unit-rooms', [MaintananceController::class, 'getUnitRooms'])->name('unit.rooms');
    Route::get('room-beds', [MaintananceController::class, 'getRoomBeds'])->name('room.beds');
});

/*
|--------------------------------------------------------------------------
| Messaging/Mailing Routes
|--------------------------------------------------------------------------
*/
// routes/web.php or your backend routes file

Route::prefix('/messaging')->name('messaging.')->middleware(['auth'])->group(function () {
    Route::get('/', [MessagingController::class, 'index'])->name('index');
    Route::get('/compose', [MessagingController::class, 'compose'])->name('compose');
    Route::get('/read/{id}', [MessagingController::class, 'read'])->name('read');

    // AJAX Routes
    Route::post('/sync', [MessagingController::class, 'sync'])->name('sync');
    Route::get('/messages', [MessagingController::class, 'getMessages'])->name('messages');
    Route::post('/send', [MessagingController::class, 'send'])->name('send');
    Route::post('/draft', [MessagingController::class, 'saveDraft'])->name('draft.save');
    Route::post('/{id}/toggle-read', [MessagingController::class, 'toggleRead'])->name('toggle.read');
    Route::post('/{id}/toggle-star', [MessagingController::class, 'toggleStar'])->name('toggle.star');
    Route::post('/{id}/move', [MessagingController::class, 'moveToFolder'])->name('move');
    Route::delete('/{id}', [MessagingController::class, 'delete'])->name('delete');
    Route::post('/bulk-action', [MessagingController::class, 'bulkAction'])->name('bulk.action');
    Route::get('/attachment/{id}', [MessagingController::class, 'downloadAttachment'])->name('attachment.download');
    Route::get('/tenants/search', [MessagingController::class, 'searchTenants'])->name('tenants.search');

    // Location-based tenant fetching
    Route::get('/units', [MessagingController::class, 'getUnits'])->name('units');
    Route::get('/rooms', [MessagingController::class, 'getRooms'])->name('rooms');
    Route::get('/tenants-by-location', [MessagingController::class, 'getTenantsByLocation'])->name('tenants.by-location');
    Route::get('/mail-template', [MessagingController::class, 'getMailTemplate'])->name('mail-template');
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
 * Mail Templates routes
 */
Route::prefix('setting/mail-templates')->name('setting.mail-templates.')->group(function () {
    Route::get('/', [MailTemplateController::class, 'index'])->name('index');
    Route::get('/create', [MailTemplateController::class, 'create'])->name('create');
    Route::post('/', [MailTemplateController::class, 'store'])->name('store');
    Route::get('/{mailTemplate}/edit', [MailTemplateController::class, 'edit'])->name('edit');
    Route::put('/{mailTemplate}', [MailTemplateController::class, 'update'])->name('update');
    Route::delete('/{mailTemplate}', [MailTemplateController::class, 'destroy'])->name('destroy');
    Route::patch('/{mailTemplate}/toggle-status', [MailTemplateController::class, 'toggleStatus'])->name('toggle-status');
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
    Route::post('/0/{id}/update-custom-fields', [LeaseDocumentController::class, 'updateCustomFields'])->name('update-custom-fields');
    Route::get('/0/{id}/download-pdf', [LeaseDocumentController::class, 'downloadPdf'])->name('download-pdf');

    // Preview document for a lease
    Route::get('/lease/{leaseId}/preview/{documentId?}', [LeaseDocumentController::class, 'previewForLease'])->name('preview-for-lease');
});


// Hierarchical Property API Routes for Lease Creation
Route::middleware(['auth', 'admin'])->group(function () {
    // Get units by property
    Route::get('/properties/{property}/units', [LeaseController::class, 'getUnits'])->name('backend.properties.units');
    Route::get('/units/{unit}/rooms', [LeaseController::class, 'getRooms'])->name('backend.units.rooms');
    Route::get('/rooms/{room}/beds', [LeaseController::class, 'getBeds'])->name('backend.rooms.beds');

    //faqq sectionn  rayyhannn
    Route::prefix('faq')->name('faq.')->group(function () {

        Route::get('/', [FaqController::class, 'index'])->name('index');
        Route::post('/store', [FaqController::class, 'store'])->name('store');
        Route::post('/update/{id}', [FaqController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [FaqController::class, 'destroy'])->name('delete');

        // STATUS TOGGLE (POST)
        Route::post('/status/{id}', [FaqController::class, 'status'])->name('status');
    });

    // item sectionn

    Route::prefix('items')->name('items.')->group(function () {

        // LIST
        Route::get('/', [ItemController::class, 'index'])->name('index');

        // CREATE
        Route::post('/store', [ItemController::class, 'store'])->name('store');

        // UPDATE
        Route::post('/update/{id}', [ItemController::class, 'update'])->name('update');

        // DELETE
        Route::delete('/delete/{id}', [ItemController::class, 'destroy'])->name('delete');

        // STATUS TOGGLE (ACTIVE / INACTIVE)
        Route::post('/status/{id}', [ItemController::class, 'status'])->name('status');
        //active data show
        Route::get('/items/active', [ItemController::class, 'activeItems']);
    });
});

// Reports Routes
Route::prefix('reports')->name('reports.')->middleware(['auth', 'admin'])->group(function () {
    // Property Report
    Route::get('/property', [PropertyReportController::class, 'index'])->name('property.index');
    Route::get('/property/data', [PropertyReportController::class, 'getData'])->name('property.data');
    Route::get('/property/export-pdf', [PropertyReportController::class, 'exportPdf'])->name('property.export.pdf');
    Route::get('/property/export-excel', [PropertyReportController::class, 'exportExcel'])->name('property.export.excel');

    // Rent Report
    Route::get('/rent', [RentReportController::class, 'index'])->name('rent.index');
    Route::get('/rent/data', [RentReportController::class, 'getData'])->name('rent.data');
    Route::get('/rent/export-pdf', [RentReportController::class, 'exportPdf'])->name('rent.export.pdf');
    Route::get('/rent/export-excel', [RentReportController::class, 'exportExcel'])->name('rent.export.excel');

    // Rent Collection Report (with Review/Confirmation)
    Route::get('/rent-collection', [RentCollectionReportController::class, 'index'])->name('rent-collection.index');
    Route::get('/rent-collection/data', [RentCollectionReportController::class, 'getData'])->name('rent-collection.data');
    Route::get('/rent-collection/summary', [RentCollectionReportController::class, 'getSummary'])->name('rent-collection.summary');
    Route::post('/rent-collection/{id}/review', [RentCollectionReportController::class, 'updateReviewStatus'])->name('rent-collection.review');
    Route::post('/rent-collection/bulk-update', [RentCollectionReportController::class, 'bulkUpdateStatus'])->name('rent-collection.bulk-update');
    Route::get('/rent-collection/export-pdf', [RentCollectionReportController::class, 'exportPdf'])->name('rent-collection.export.pdf');
    Route::get('/rent-collection/export-excel', [RentCollectionReportController::class, 'exportExcel'])->name('rent-collection.export.excel');

    // Tenant Report
    Route::get('/tenant', [TenantReportController::class, 'index'])->name('tenant.index');
    Route::get('/tenant/data', [TenantReportController::class, 'getData'])->name('tenant.data');
    Route::get('/tenant/export-pdf', [TenantReportController::class, 'exportPdf'])->name('tenant.export.pdf');
    Route::get('/tenant/export-excel', [TenantReportController::class, 'exportExcel'])->name('tenant.export.excel');
});

// System Monitoring Routes (Superadmin Only)
Route::prefix('system-monitor')->name('system-monitor.')->group(function () {
    // Web Views
    Route::get('/', [SystemMonitorController::class, 'index'])->name('index');
    Route::get('/health', [SystemMonitorController::class, 'health'])->name('health');
    Route::get('/resources', [SystemMonitorController::class, 'resources'])->name('resources');
    Route::get('/database', [SystemMonitorController::class, 'database'])->name('database');
    Route::get('/applications', [SystemMonitorController::class, 'applications'])->name('applications');
    Route::get('/activity', [SystemMonitorController::class, 'activity'])->name('activity');
    Route::get('/security', [SystemMonitorController::class, 'security'])->name('security');
    Route::get('/errors', [SystemMonitorController::class, 'errors'])->name('errors');

    // API Endpoints (JSON)
    // Route::get('/api/overview', [SystemMonitorController::class, 'getOverview'])->name('api.overview');
    // Route::get('/api/health', [SystemMonitorController::class, 'getHealthStatus'])->name('api.health');
    // Route::get('/api/resources', [SystemMonitorController::class, 'getResources'])->name('api.resources');
    // Route::get('/api/database', [SystemMonitorController::class, 'getDatabaseInfo'])->name('api.database');
    // Route::get('/api/stats', [SystemMonitorController::class, 'getApplicationStats'])->name('api.stats');
    // Route::get('/api/activity', [SystemMonitorController::class, 'getUserActivity'])->name('api.activity');
});
