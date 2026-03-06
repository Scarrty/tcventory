<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreValuationRequest;
use App\Models\Valuation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValuationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Valuation::class, 'valuation');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $valuations = Valuation::query()->with('inventoryItem')->latest('valued_at')->paginate(max(1, min($perPage, 100)));

        return response()->json(['data' => $valuations->items(), 'meta' => [
            'current_page' => $valuations->currentPage(),
            'per_page' => $valuations->perPage(),
            'total' => $valuations->total(),
        ]]);
    }

    public function store(StoreValuationRequest $request): JsonResponse
    {
        $valuation = Valuation::query()->create([
            ...$request->validated(),
            'currency' => strtoupper((string) ($request->validated('currency') ?? 'EUR')),
            'source' => $request->validated('source') ?? 'manual',
        ]);

        return response()->json(['data' => $valuation->load('inventoryItem')], 201);
    }
}
