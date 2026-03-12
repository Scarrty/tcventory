# PLANS

## Schema

Each plan entry in this file uses the following structure:

1. **Plan ID / Title**
2. **Status** (`draft`, `in_progress`, `blocked`, `completed`)
3. **Context Snapshot** (current implementation reality + gaps)
4. **Objectives & Non-Objectives**
5. **Workstreams** (ordered, with concrete deliverables)
6. **Verification Evidence Requirements** (what must be proven before completion)
7. **Risks & Mitigations**
8. **Exit Criteria (Definition of Done)**
9. **Decision Log** (important tradeoffs and scope decisions)

---

## PLAN-2026-03-12-NEXT-PHASE — Phase 3 Completion (Finance & Valuation) + Phase 4 Readiness

- **Status:** `completed`
- **Owner:** Codex planning pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

Project evidence indicates:

- Phase 2 (Inventory & Catalog MVP) is in final consolidation and largely implemented.
- Phase 3 (Finance & Valuation) is partially implemented: purchases/sales/valuations exist plus `reports/finance-summary`.
- Current finance summary is intentionally simple and not yet periodized/channel-aware.
- Phase 4 prerequisites (audit model tables) exist, but a systemwide append-only audit hash chain is not yet enforced in write flows.

**Conclusion:** the most valuable “next phase” is to finish Phase 3 with production-grade finance reporting and quality/contract hardening, while creating implementation-ready handoff criteria for Phase 4 audit operations.

### 2) Objectives

1. Complete Phase-3 reporting depth so finance outcomes become decision-grade.
2. Standardize finance API contracts (errors, pagination, filtering semantics).
3. Stabilize quality gates for finance-related changes (tests/static analysis/style).
4. Define and de-risk the transition into Phase 4 audit hardening.

### 3) Non-Objectives

- No marketplace integrations (Phase 5 scope).
- No Meilisearch/search expansion.
- No broad UI redesign; API-first completion and reliability are prioritized.

### 4) Workstreams and Deliverables

#### WS1 — Finance Domain Semantics & Reporting Model

- Define canonical KPI glossary (gross, net, realized P/L, unrealized P/L, fee burden, tax burden).
- Specify period model (`day`, `week`, `month`, custom date range) and channel segmentation.
- Introduce explicit cost-allocation rules (shipping/fees/taxes) for line-level and aggregate reporting.

**Deliverables**
- `docs/finance-reporting-spec.md` (new): KPI formulas + examples.
- Decision record on valuation method assumptions (latest valuation vs weighted/basis strategy).

#### WS2 — API Surface for Advanced Finance Reporting

- Expand reporting endpoints (or parameterized extension of existing one) for:
  - periodized P/L
  - channel-based drilldown
  - realized vs unrealized split
  - optional product/set/game grouping
- Add request validation + deterministic error format.
- Preserve backward compatibility for existing `reports/finance-summary` consumers.

**Deliverables**
- Route/controller/request updates for advanced reporting.
- Updated `API_DOCS.md` with request/response examples and failure contracts.

#### WS3 — Data Integrity & Performance Baseline

- Verify aggregation queries with realistic data volumes.
- Add/adjust DB indexes supporting period and channel filters.
- Ensure aggregation queries are deterministic and tested against edge cases (null fields, empty periods, mixed currencies if relevant).

**Deliverables**
- Migration(s) for reporting-oriented indexes where justified.
- Performance notes with target budgets (query count/time on seeded fixture).

#### WS4 — Quality Gate Hardening for Finance Flows

- Expand feature tests for new reporting endpoints and filter combinations.
- Add unit/service tests for KPI and allocation calculations.
- Ensure Pint/PHPStan/test suite pass in local + CI configuration.

**Deliverables**
- Finance-focused tests covering positive, authorization, validation, and edge-case behavior.
- CI/QA command evidence recorded in `PROGRESS.md` once implemented.

#### WS5 — Phase 4 Readiness Slice (Audit Handoff)

- Define mandatory audit hooks for finance write flows (purchase/sale/valuation create events).
- Document hash-chain integration points without implementing full Phase-4 scope yet.

**Deliverables**
- `docs/audit-phase4-readiness.md` (new): write-flow coverage map and backlog.
- Explicit handoff checklist from Phase 3 completion into Phase 4 execution.

### 5) Verification Evidence Requirements

Before marking this plan complete, provide evidence for:

1. **Contract correctness**
   - Endpoint matrix with supported filters/params/status codes.
2. **Calculation correctness**
   - Test fixtures that prove KPI math and allocation rules.
3. **Authorization & validation**
   - Forbidden and unprocessable scenarios for each new/extended endpoint.
4. **Performance guardrails**
   - Captured query/performance snapshot for representative report requests.
5. **Quality gates**
   - Green runs for:
     - `php artisan test`
     - `vendor/bin/pint --test`
     - `vendor/bin/phpstan analyse --memory-limit=1G`

### 6) Risks and Mitigations

- **Risk:** KPI interpretation drift across docs and API.
  - **Mitigation:** single source of truth in `docs/finance-reporting-spec.md` + API examples derived from same formulas.
- **Risk:** Reporting queries become slow under growth.
  - **Mitigation:** benchmark early with seeded volumes; index before exposing broad filters.
- **Risk:** Phase-3 scope creeps into full Phase-4 implementation.
  - **Mitigation:** enforce non-objectives and keep Phase-4 output to readiness artifacts only.

### 7) Exit Criteria (Definition of Done)

This plan is done when:

1. Advanced finance reporting endpoints/parameters are implemented and documented.
2. KPI calculations and allocation logic are covered by automated tests.
3. Finance contracts are consistent with project API conventions.
4. Quality gates pass in a reproducible environment.
5. Phase-4 audit handoff documentation is complete and references concrete integration points.

### 8) Decision Log

- **2026-03-12:** Selected “Phase 3 completion + Phase 4 readiness” as the next phase because Phase 3 already has working primitives and offers the highest near-term product value with contained scope.
- **2026-03-12:** Chose API-first reporting depth over UI expansion to minimize surface area and maximize integration utility.

### 9) Execution Checklist (Current Run)

- [x] Reconfirm scope: implement advanced `reports/finance-summary` behavior with backward-compatible fields.
- [x] Add finance reporting request validation + deterministic filter semantics (period/date/channel/grouping).
- [x] Implement KPI expansion (gross/net/realized/unrealized/fee burden/tax burden) and channel drilldown payload.
- [x] Add reporting-oriented DB indexes for period/channel-heavy queries.
- [x] Update docs (`API_DOCS.md`, `docs/finance-reporting-spec.md`, `docs/audit-phase4-readiness.md`).
- [x] Extend finance feature tests for authorization, validation, calculations, and filters.
- [x] Capture verification evidence and set status to `completed` if all quality gates pass.

### 10) Verification Evidence Log (Current Run)

- `php artisan test tests/Feature/Api/FinanceApiTest.php` ✅ (5 tests, 40 assertions).
- `vendor/bin/pint --test` ✅.
- `vendor/bin/phpstan analyse --memory-limit=1G` ✅.
- `php artisan test` ⚠️ failed due to environment/frontend setup gap (`public/build/manifest.json` missing and `.env` file-read warnings in non-API feature tests).
