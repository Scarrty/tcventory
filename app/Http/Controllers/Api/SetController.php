<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSetRequest;
use App\Http\Requests\Api\UpdateSetRequest;
use App\Models\Set;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetController extends Controller
{
    public function __construct()
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
        $set->update($request->validated());

        return response()->json([
            'data' => $set->fresh()->load('game'),
        ]);
    }
}
