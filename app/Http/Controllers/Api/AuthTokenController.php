<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateAuthTokenRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class AuthTokenController extends Controller
{
    public function store(CreateAuthTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $abilities = $validated['abilities'] ?? ['*'];
        $expiresAt = isset($validated['expires_in_minutes'])
            ? CarbonImmutable::now()->addMinutes($validated['expires_in_minutes'])
            : null;

        $token = $request->user()->createToken(
            name: $validated['token_name'],
            abilities: $abilities,
            expiresAt: $expiresAt,
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'expires_at' => $expiresAt?->toIso8601String(),
        ]);
    }
}
