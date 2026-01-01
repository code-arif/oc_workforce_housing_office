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

});
