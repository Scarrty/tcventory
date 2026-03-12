# Finance Reporting Spec

## Scope

This document defines KPI formulas and filter semantics for `GET /api/v1/reports/finance-summary`.

## KPI Glossary

- `purchase_total`: sum of `purchases.total_amount` in selected period.
- `sale_gross_total`: sum of `sales.gross_amount` in selected period.
- `sale_net_total`: sum of `sales.net_amount` in selected period.
- `realized_profit_loss`: `sale_net_total - purchase_total`.
- `latest_inventory_valuation`: sum of the latest `valuations.value_amount` per `inventory_item_id` (global latest, not periodized).
- `unrealized_profit_loss`: `latest_inventory_valuation - purchase_total`.
- `fee_burden_total`: sum of `sales.fee_amount` in selected period.
- `tax_burden_total`: sum of `sales.tax_amount` in selected period.

## Filters

- `period`: `all | day | week | month | custom`
- `from_date` and `to_date`: required only with `period=custom`.
- `channel`: optional exact filter on `sales.channel`.
- `group_by`: `none | channel`

`period=day|week|month` uses UTC boundaries derived from request time.

## Grouping

When `group_by=channel`, the response includes `breakdown.by_channel` with:

- `channel`
- `gross_total`
- `net_total`
- `fee_total`
- `tax_total`

Null channels are normalized to `unknown`.

## Backward Compatibility

The response still exposes top-level legacy fields:

- `purchase_total`
- `sale_net_total`
- `realized_profit_loss`
- `latest_inventory_valuation`
