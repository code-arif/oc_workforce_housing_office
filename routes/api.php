<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthenticationController;

//health-check
Route::get("/check", function () {
    return "All Right 👍";
});

//Guest user routes
Route::group(['middleware' => 'guest:api'], function () {

    // Login & Register
    Route::post('/login', [AuthenticationController::class, 'login']);

    // Property Creation - Unit/Room/Bed API
    Route::get('/unit/{unitId}/rooms', function ($unitId) {
        $unit = \App\Models\Unit::with('rooms')->find($unitId);
        if (!$unit) {
            return response()->json(['rooms' => []]);
        }
        return response()->json(['rooms' => $unit->rooms]);
    })->name('api.unit.rooms');

    Route::post('/rooms/beds', function (\Illuminate\Http\Request $request) {
        $roomId = $request->input('room_id');
        if (empty($roomId)) {
            return response()->json(['beds' => []]);
        }
        $beds = \App\Models\Bed::where('room_id', $roomId)->with('room')->get();
        return response()->json(['beds' => $beds]);
    })->name('api.rooms.beds');

    
});


Route::group(['middleware' => 'auth:api'], function () {
    //User logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    // // Lease Templates - Document Upload & Management
    // Route::apiResource('lease-templates', \App\Http\Controllers\Api\LeaseTemplateController::class);
    
    // // Additional lease template routes
    // Route::group(['prefix' => 'lease-templates'], function () {
    //     Route::post('{template}/upload-document', [\App\Http\Controllers\Api\LeaseTemplateController::class, 'uploadDocument'])->name('lease-templates.upload-document');
    //     Route::get('{template}/preview', [\App\Http\Controllers\Api\LeaseTemplateController::class, 'preview'])->name('lease-templates.preview');
    //     Route::get('available-data-sources', [\App\Http\Controllers\Api\LeaseTemplateController::class, 'getAvailableDataSources'])->name('lease-templates.available-data-sources');
    //     Route::get('{template}/preview-with-data', [\App\Http\Controllers\Api\LeaseTemplateController::class, 'previewWithData'])->name('lease-templates.preview-with-data');
    //     Route::get('{template}/extract-placeholders', [\App\Http\Controllers\Api\LeaseTemplateController::class, 'extractPlaceholders'])->name('lease-templates.extract-placeholders');
    // });

    // // Field Mappings
    // Route::group(['prefix' => 'lease-templates/{template}/field-mappings'], function () {
    //     Route::get('', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'index'])->name('field-mappings.index');
    //     Route::post('', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'store'])->name('field-mappings.store');
    //     Route::get('suggestions', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'suggestions'])->name('field-mappings.suggestions');
    //     Route::get('available-data-sources', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'availableDataSources'])->name('field-mappings.available-data-sources');
    //     Route::get('validate-completeness', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'validateCompleteness'])->name('field-mappings.validate-completeness');
    //     Route::delete('all', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'deleteAll'])->name('field-mappings.delete-all');
    //     Route::get('{fieldMapping}', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'show'])->name('field-mappings.show');
    //     Route::put('{fieldMapping}', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'update'])->name('field-mappings.update');
    //     Route::delete('{fieldMapping}', [\App\Http\Controllers\Api\LeaseTemplateFieldMappingController::class, 'destroy'])->name('field-mappings.destroy');
    // });

});
