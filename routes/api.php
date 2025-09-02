
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\TableController;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/scan-table', [OrderController::class, 'scanTable']);

    Route::get('/menu/{restaurant}/{session}', [OrderController::class, 'showMenu'])
         ->name('menu.show');

    Route::post('/orders', [OrderController::class, 'store']);

    Route::get('/orders/{orderNumber}/track', [OrderController::class, 'track']);

    Route::get('/queue/{restaurantId}', [OrderController::class, 'queueStatus']);
});

Route::group(['prefix' => 'v1/staff', 'middleware' => 'auth:staff'], function () {
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::get('/kitchen/dashboard', [KitchenController::class, 'dashboard']);
    Route::get('/kitchen/station/{station}', [KitchenController::class, 'stationDisplay']);
    Route::get('/kitchen/queue', [KitchenController::class, 'getQueue']);

    Route::post('/kitchen/items/{orderItem}/start', [KitchenController::class, 'startItem']);
    Route::post('/kitchen/items/{orderItem}/complete', [KitchenController::class, 'completeItem']);

    Route::get('/kitchen/realtime/{restaurantId}', [KitchenController::class, 'getRealTimeData']);
});