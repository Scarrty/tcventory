# Audit Phase 4 Readiness

## Purpose

Define the finance write flows that must emit append-only audit chain events in Phase 4.

## Mandatory Audit Hooks

1. Purchase creation (`POST /api/v1/purchases`)
   - Event type: `finance.purchase.created`
   - Payload minimum: purchase id, request key, user id, line-item snapshot, totals.
2. Sale creation (`POST /api/v1/sales`)
   - Event type: `finance.sale.created`
   - Payload minimum: sale id, request key, user id, line-item snapshot, totals.
3. Valuation creation (`POST /api/v1/valuations`)
   - Event type: `finance.valuation.created`
   - Payload minimum: valuation id, inventory item id, value/source/valued_at, user id.

## Hash Chain Integration Points

- Emit event after DB transaction commit to avoid phantom records.
- Include previous audit event hash + current canonicalized payload hash.
- Persist chain metadata in audit tables from `2026_01_01_000004_create_audit_and_ledger_tables.php`.

## Handoff Checklist

- [ ] Implement audit event writer service with canonical payload serializer.
- [ ] Wire purchase/sale/valuation controllers to emit post-commit events.
- [ ] Add integrity verification command for hash chain continuity.
- [ ] Add feature tests asserting event presence and chain links.
- [ ] Document operational playbook for forensic export.
