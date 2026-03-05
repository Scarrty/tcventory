<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', static fn (): array => [
    'app' => 'TCventory',
    'status' => 'ok',
]);
