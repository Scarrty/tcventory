<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGameRequest;
use App\Http\Requests\Api\UpdateGameRequest;
use App\Models\Game;
use App\Services\Audit\HashChainAuditLogger;
use App\Services\Catalog\DeleteGameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct(private readonly HashChainAuditLogger $auditLogger)
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
        $actor = $request->user();

        DB::afterCommit(function () use ($game, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.game.created',
                auditable: $game,
                changes: [
                    'game_id' => $game->id,
                    'after' => [
                        'name' => $game->name,
                        'slug' => $game->slug,
                    ],
                ],
                context: ['source' => 'api.v1.games.store'],
                actor: $actor,
            );
        });

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
        $before = [
            'name' => $game->name,
            'slug' => $game->slug,
        ];

        $game->update($request->validated());
        $updatedGame = $game->fresh();
        $actor = $request->user();

        DB::afterCommit(function () use ($game, $before, $updatedGame, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.game.updated',
                auditable: $game,
                changes: [
                    'game_id' => $game->id,
                    'before' => $before,
                    'after' => [
                        'name' => $updatedGame?->name,
                        'slug' => $updatedGame?->slug,
                    ],
                ],
                context: ['source' => 'api.v1.games.update'],
                actor: $actor,
            );
        });

        return response()->json([
            'data' => $updatedGame,
        ]);
    }

    public function destroy(Request $request, Game $game, DeleteGameService $service): JsonResponse
    {
        $this->authorize('delete', $game);

        $changes = [
            'game_id' => $game->id,
            'before' => [
                'name' => $game->name,
                'slug' => $game->slug,
            ],
        ];

        $service->execute($game);
        $actor = $request->user();

        DB::afterCommit(function () use ($game, $changes, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.game.deleted',
                auditable: $game,
                changes: $changes,
                context: ['source' => 'api.v1.games.destroy'],
                actor: $actor,
            );
        });

        return response()->json([], 204);
    }
}
