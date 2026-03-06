<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SetController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', static fn (): array => [
        'app' => 'TCventory API',
        'status' => 'ok',
    ]);

    Route::post('/tokens', [AuthTokenController::class, 'store'])
        ->middleware('auth:sanctum');

    Route::get('/me', CurrentUserController::class)
        ->middleware(['auth:sanctum', 'role:user|admin']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('games', GameController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('sets', SetController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('products', ProductController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('inventory-items', InventoryItemController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('inventory-items/{inventory_item}/transfer', [InventoryItemController::class, 'transfer']);
        Route::post('inventory-items/{inventory_item}/adjust-stock', [InventoryItemController::class, 'adjustStock']);
    });
});
