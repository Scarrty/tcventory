<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSetRequest;
use App\Http\Requests\Api\UpdateSetRequest;
use App\Models\Set;
use App\Services\Audit\HashChainAuditLogger;
use App\Services\Catalog\DeleteSetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetController extends Controller
{
    public function __construct(private readonly HashChainAuditLogger $auditLogger)
    {
        $this->authorizeResource(Set::class, 'set');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $sets = Set::query()->with('game')->latest('id')->paginate(max(1, min($perPage, 100)));

        return response()->json([
            'data' => $sets->items(),
            'meta' => [
                'current_page' => $sets->currentPage(),
                'per_page' => $sets->perPage(),
                'total' => $sets->total(),
            ],
        ]);
    }

    public function store(StoreSetRequest $request): JsonResponse
    {
        $set = Set::query()->create($request->validated())->load('game');
        $actor = $request->user();

        DB::afterCommit(function () use ($set, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.set.created',
                auditable: $set,
                changes: [
                    'set_id' => $set->id,
                    'after' => [
                        'game_id' => $set->game_id,
                        'name' => $set->name,
                        'code' => $set->code,
                    ],
                ],
                context: ['source' => 'api.v1.sets.store'],
                actor: $actor,
            );
        });

        return response()->json([
            'data' => $set,
        ], 201);
    }

    public function show(Set $set): JsonResponse
    {
        return response()->json([
            'data' => $set->load('game'),
        ]);
    }

    public function update(UpdateSetRequest $request, Set $set): JsonResponse
    {
        $before = [
            'game_id' => $set->game_id,
            'name' => $set->name,
            'code' => $set->code,
        ];

        $set->update($request->validated());
        $updatedSet = $set->fresh()->load('game');
        $actor = $request->user();

        DB::afterCommit(function () use ($set, $before, $updatedSet, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.set.updated',
                auditable: $set,
                changes: [
                    'set_id' => $set->id,
                    'before' => $before,
                    'after' => [
                        'game_id' => $updatedSet->game_id,
                        'name' => $updatedSet->name,
                        'code' => $updatedSet->code,
                    ],
                ],
                context: ['source' => 'api.v1.sets.update'],
                actor: $actor,
            );
        });

        return response()->json([
            'data' => $updatedSet,
        ]);
    }

    public function destroy(Request $request, Set $set, DeleteSetService $service): JsonResponse
    {
        $this->authorize('delete', $set);

        $changes = [
            'set_id' => $set->id,
            'before' => [
                'game_id' => $set->game_id,
                'name' => $set->name,
                'code' => $set->code,
            ],
        ];

        $service->execute($set);
        $actor = $request->user();

        DB::afterCommit(function () use ($set, $changes, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.set.deleted',
                auditable: $set,
                changes: $changes,
                context: ['source' => 'api.v1.sets.destroy'],
                actor: $actor,
            );
        });

        return response()->json([], 204);
    }
}
