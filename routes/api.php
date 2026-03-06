<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\CurrentUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', static fn (): array => [
        'app' => 'TCventory API',
        'status' => 'ok',
    ]);

    Route::post('/tokens', [AuthTokenController::class, 'store'])
        ->middleware('auth');

    Route::get('/me', CurrentUserController::class)
        ->middleware(['auth:sanctum', 'role:user|admin']);
});
