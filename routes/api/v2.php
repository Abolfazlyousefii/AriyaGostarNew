<?php

use App\Http\Controllers\Api\V2\Sync\ImageSyncController;
use App\Http\Controllers\Api\V2\Sync\PaidOrderController;
use App\Http\Controllers\Api\V2\Sync\ProductSyncController;
use App\Http\Controllers\Api\V2\Sync\RegisteredCustomerController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v2'], function () {
    Route::group(['prefix' => 'sync'], function () {
        Route::post('products', ProductSyncController::class);
        Route::get('orders', PaidOrderController::class);
        Route::get('customers', RegisteredCustomerController::class);

        Route::post('images', [ImageSyncController::class, 'pending']);
        Route::post('images/verify', [ImageSyncController::class, 'verify']);
    });
})->withoutMiddleware(['throttle:api']);
