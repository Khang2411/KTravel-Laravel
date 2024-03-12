<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Room;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
    });
});


Route::get('/v1/rooms', function () {
    return Room::paginate(10);
});
Route::get('/v1/categories', function () {
    return response()->json(["data" => Category::all()]);
});
Route::get('/v1/amenities', function () {
    return response()->json(["data" => Amenity::all()]);
});

Route::get('/v1/test', function () {
    return response()->json(["data" => Test::all()]);
});

Route::post('/v1/test/add', function () {
    return Test::create([ 'name' => request()->post('name')]);
});
