<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Models\Product;
use App\Services\Audit\HashChainAuditLogger;
use App\Services\Catalog\DeleteProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(private readonly HashChainAuditLogger $auditLogger)
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $products = Product::query()->with(['game', 'set'])->latest('id')->paginate(max(1, min($perPage, 100)));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated())->load(['game', 'set']);
        $actor = $request->user();

        DB::afterCommit(function () use ($product, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.product.created',
                auditable: $product,
                changes: [
                    'product_id' => $product->id,
                    'after' => [
                        'game_id' => $product->game_id,
                        'set_id' => $product->set_id,
                        'name' => $product->name,
                        'product_type' => $product->product_type,
                    ],
                ],
                context: ['source' => 'api.v1.products.store'],
                actor: $actor,
            );
        });

        return response()->json([
            'data' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->load(['game', 'set']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $before = [
            'game_id' => $product->game_id,
            'set_id' => $product->set_id,
            'name' => $product->name,
            'product_type' => $product->product_type,
            'rarity' => $product->rarity,
        ];

        $product->update($request->validated());
        $updatedProduct = $product->fresh()->load(['game', 'set']);
        $actor = $request->user();

        DB::afterCommit(function () use ($product, $before, $updatedProduct, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.product.updated',
                auditable: $product,
                changes: [
                    'product_id' => $product->id,
                    'before' => $before,
                    'after' => [
                        'game_id' => $updatedProduct->game_id,
                        'set_id' => $updatedProduct->set_id,
                        'name' => $updatedProduct->name,
                        'product_type' => $updatedProduct->product_type,
                        'rarity' => $updatedProduct->rarity,
                    ],
                ],
                context: ['source' => 'api.v1.products.update'],
                actor: $actor,
            );
        });

        return response()->json([
            'data' => $updatedProduct,
        ]);
    }

    public function destroy(Request $request, Product $product, DeleteProductService $service): JsonResponse
    {
        $this->authorize('delete', $product);

        $changes = [
            'product_id' => $product->id,
            'before' => [
                'game_id' => $product->game_id,
                'set_id' => $product->set_id,
                'name' => $product->name,
                'product_type' => $product->product_type,
            ],
        ];

        $service->execute($product);
        $actor = $request->user();

        DB::afterCommit(function () use ($product, $changes, $actor): void {
            $this->auditLogger->log(
                eventType: 'catalog.product.deleted',
                auditable: $product,
                changes: $changes,
                context: ['source' => 'api.v1.products.destroy'],
                actor: $actor,
            );
        });

        return response()->json([], 204);
    }
}
