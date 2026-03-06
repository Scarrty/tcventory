<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGameRequest;
use App\Http\Requests\Api\UpdateGameRequest;
use App\Models\Game;
use App\Services\Catalog\DeleteGameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Game::class, 'game');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $games = Game::query()->latest('id')->paginate(max(1, min($perPage, 100)));

        return response()->json([
            'data' => $games->items(),
            'meta' => [
                'current_page' => $games->currentPage(),
                'per_page' => $games->perPage(),
                'total' => $games->total(),
            ],
        ]);
    }

    public function store(StoreGameRequest $request): JsonResponse
    {
        $game = Game::query()->create($request->validated());

        return response()->json([
            'data' => $game,
        ], 201);
    }

    public function show(Game $game): JsonResponse
    {
        return response()->json([
            'data' => $game,
        ]);
    }

    public function update(UpdateGameRequest $request, Game $game): JsonResponse
    {
        $game->update($request->validated());

        return response()->json([
            'data' => $game->fresh(),
        ]);
    }

    public function destroy(Game $game, DeleteGameService $service): JsonResponse
    {
        $this->authorize('delete', $game);

        $service->execute($game);

        return response()->json([], 204);
    }
}
