<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FinanceSummaryRequest;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Valuation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class FinanceReportController extends Controller
{
    public function __invoke(FinanceSummaryRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Purchase::class);

        $filters = $this->resolveFilters($request);

        $purchaseQuery = Purchase::query();
        $saleQuery = Sale::query();
        $this->applyPeriodToPurchases($purchaseQuery, $filters['from'], $filters['to']);
        $this->applyPeriodToSales($saleQuery, $filters['from'], $filters['to']);
        if ($filters['channel'] !== null) {
            $saleQuery->where('channel', $filters['channel']);
        }

        $purchaseTotal = (float) $purchaseQuery->sum('total_amount');
        $saleGrossTotal = (float) $saleQuery->sum('gross_amount');
        $saleNetTotal = (float) $saleQuery->sum('net_amount');
        $saleFeeTotal = (float) $saleQuery->sum('fee_amount');
        $saleTaxTotal = (float) $saleQuery->sum('tax_amount');

        $latestValuationTotal = (float) Valuation::query()
            ->selectRaw('COALESCE(SUM(value_amount),0) as total')
            ->whereIn('id', Valuation::query()->selectRaw('MAX(id)')->groupBy('inventory_item_id'))
            ->value('total');

        $realizedProfitLoss = $saleNetTotal - $purchaseTotal;

        $response = [
            'period' => [
                'type' => $filters['period'],
                'from_date' => $filters['from']?->toDateString(),
                'to_date' => $filters['to']?->toDateString(),
            ],
            'filters' => [
                'channel' => $filters['channel'],
                'group_by' => $filters['groupBy'],
            ],
            'kpis' => [
                'purchase_total' => round($purchaseTotal, 2),
                'sale_gross_total' => round($saleGrossTotal, 2),
                'sale_net_total' => round($saleNetTotal, 2),
                'realized_profit_loss' => round($realizedProfitLoss, 2),
                'unrealized_profit_loss' => round($latestValuationTotal - $purchaseTotal, 2),
                'latest_inventory_valuation' => round($latestValuationTotal, 2),
                'fee_burden_total' => round($saleFeeTotal, 2),
                'tax_burden_total' => round($saleTaxTotal, 2),
            ],
            'breakdown' => [
                'by_channel' => $filters['groupBy'] === 'channel' ? $this->channelBreakdown($filters['from'], $filters['to']) : [],
            ],
        ];

        return response()->json(['data' => $response]);
    }

    /**
     * @return array{period:string, from:?CarbonImmutable, to:?CarbonImmutable, channel:?string, groupBy:string}
     */
    private function resolveFilters(FinanceSummaryRequest $request): array
    {
        $period = (string) $request->input('period', 'all');
        $today = CarbonImmutable::now()->utc();

        $from = null;
        $to = null;

        if ($period === 'day') {
            $from = $today->startOfDay();
            $to = $today->endOfDay();
        } elseif ($period === 'week') {
            $from = $today->startOfWeek();
            $to = $today->endOfWeek();
        } elseif ($period === 'month') {
            $from = $today->startOfMonth();
            $to = $today->endOfMonth();
        } elseif ($period === 'custom') {
            $from = CarbonImmutable::parse((string) $request->input('from_date'))->startOfDay();
            $to = CarbonImmutable::parse((string) $request->input('to_date'))->endOfDay();
        }

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'channel' => $request->filled('channel') ? (string) $request->input('channel') : null,
            'groupBy' => (string) $request->input('group_by', 'none'),
        ];
    }

    private function applyPeriodToPurchases(Builder $query, ?CarbonImmutable $from, ?CarbonImmutable $to): void
    {
        if ($from === null || $to === null) {
            return;
        }

        $query->whereBetween('purchased_at', [$from, $to]);
    }

    private function applyPeriodToSales(Builder $query, ?CarbonImmutable $from, ?CarbonImmutable $to): void
    {
        if ($from === null || $to === null) {
            return;
        }

        $query->whereBetween('sold_at', [$from, $to]);
    }

    /**
     * @return array<int, array<string, float|string|null>>
     */
    private function channelBreakdown(?CarbonImmutable $from, ?CarbonImmutable $to): array
    {
        $query = Sale::query()
            ->selectRaw('COALESCE(channel, ?) as channel', ['unknown'])
            ->selectRaw('COALESCE(SUM(gross_amount),0) as gross_total')
            ->selectRaw('COALESCE(SUM(net_amount),0) as net_total')
            ->selectRaw('COALESCE(SUM(fee_amount),0) as fee_total')
            ->selectRaw('COALESCE(SUM(tax_amount),0) as tax_total')
            ->groupBy('channel')
            ->orderBy('channel');

        $this->applyPeriodToSales($query, $from, $to);

        return $query->get()->map(static fn ($row): array => [
            'channel' => $row->channel,
            'gross_total' => round((float) $row->gross_total, 2),
            'net_total' => round((float) $row->net_total, 2),
            'fee_total' => round((float) $row->fee_total, 2),
            'tax_total' => round((float) $row->tax_total, 2),
        ])->all();
    }
}
