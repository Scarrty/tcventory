<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Valuation;
use Illuminate\Http\JsonResponse;

class FinanceReportController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Purchase::class);

        $purchaseTotal = (float) Purchase::query()->sum('total_amount');
        $saleNetTotal = (float) Sale::query()->sum('net_amount');
        $latestValuationTotal = (float) Valuation::query()
            ->selectRaw('COALESCE(SUM(value_amount),0) as total')
            ->whereIn('id', Valuation::query()->selectRaw('MAX(id)')->groupBy('inventory_item_id'))
            ->value('total');

        return response()->json([
            'data' => [
                'purchase_total' => round($purchaseTotal, 2),
                'sale_net_total' => round($saleNetTotal, 2),
                'realized_profit_loss' => round($saleNetTotal - $purchaseTotal, 2),
                'latest_inventory_valuation' => round($latestValuationTotal, 2),
            ],
        ]);
    }
}
