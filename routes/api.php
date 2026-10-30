<?php

use App\Http\Controllers\Api\Integrations\Inventory\InventoryEventController;
use Illuminate\Support\Facades\Route;

Route::post('/integrations/inventory/events', InventoryEventController::class)
    ->name('integrations.inventory.events');

require __DIR__.'/api/v1.php';
require __DIR__.'/api/v2.php';