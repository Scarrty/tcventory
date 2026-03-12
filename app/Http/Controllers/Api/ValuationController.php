<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithApiPagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreValuationRequest;
use App\Models\Valuation;
use App\Services\Audit\HashChainAuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValuationController extends Controller
{
    use InteractsWithApiPagination;

    public function __construct(private readonly HashChainAuditLogger $auditLogger)
    {
        $this->authorizeResource(Valuation::class, 'valuation');
    }

    public function index(Request $request): JsonResponse
    {
        $valuations = Valuation::query()->with('inventoryItem')->latest('valued_at')->paginate($this->resolvePerPage($request));

        return $this->paginatedResponse($valuations);
    }

    public function store(StoreValuationRequest $request): JsonResponse
    {
        $valuation = Valuation::query()->create([
            ...$request->validated(),
            'currency' => strtoupper((string) ($request->validated('currency') ?? 'EUR')),
            'source' => $request->validated('source') ?? 'manual',
        ]);

        $actor = $request->user();
        DB::afterCommit(function () use ($valuation, $actor): void {
            $this->auditLogger->log(
                eventType: 'finance.valuation.created',
                auditable: $valuation,
                changes: [
                    'valuation_id' => $valuation->id,
                    'inventory_item_id' => $valuation->inventory_item_id,
                    'value_amount' => $valuation->value_amount,
                    'currency' => $valuation->currency,
                    'source' => $valuation->source,
                    'valued_at' => $valuation->valued_at?->toISOString(),
                ],
                context: ['source' => 'api.v1.valuations.store'],
                actor: $actor,
            );
        });

        return response()->json(['data' => $valuation->load('inventoryItem')], 201);
    }
}
