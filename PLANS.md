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

## PLAN-2026-03-12-DOC-SYNC — Documentation Structure Alignment (README-anchored)

- **Status:** `completed`
- **Owner:** Codex documentation maintenance pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

- `README.md` defines the current onboarding narrative and expected reading flow.
- `docs/README.md` defines documentation authority boundaries (especially: `PROGRESS.md` as live status source).
- Existing completed implementation plans in this file indicate a project state ahead of the currently published 2026-03-06 status wording in several top-level docs.

### 2) Objectives

1. Expand planning artifacts so documentation maintenance itself is tracked with the same rigor as code work.
2. Preserve the existing documentation structure (`README.md` → `docs/README.md` map) while capturing current implementation reality from completed plans.
3. Provide a clean handoff checklist for synchronizing `README.md`, `PROGRESS.md`, and `ROADMAP.md` with the latest completed delivery phases.

### 3) Non-Objectives

- No rewrite of documentation architecture or ownership model.
- No speculative future-scope expansion beyond already completed implementation evidence.
- No API/runtime code changes.

### 4) Workstreams and Deliverables

#### WS1 — Baseline Reconciliation

- Cross-check status statements between `README.md`, `docs/README.md`, and existing completed plans in `PLANS.md`.
- Identify where live status text lags behind completed implementation work.

**Deliverables**
- Documented discrepancy list in this plan's execution checklist.

#### WS2 — PLANS.md Expansion for Documentation Governance

- Add a dedicated plan entry for documentation alignment work, using canonical schema.
- Record current project status snapshot inferred from completed plans (Phase 3 completion and scoped Phase 4 finance audit-chain implementation).

**Deliverables**
- This plan entry with clear objectives, risks, and verification evidence.

#### WS3 — Status/Progress Synchronization Backlog

- Define next concrete update targets so status documents can be refreshed without changing their role boundaries.

**Deliverables**
- Explicit follow-up tasks for:
  - `README.md` “Aktueller Stand” section,
  - `PROGRESS.md` phase percentages/work packages,
  - `ROADMAP.md` phase descriptions for completed Phase-3 and partial Phase-4 scope.

### 5) Verification Evidence Requirements

Before this plan can be considered complete:

1. `PLANS.md` includes this expanded, schema-compliant documentation-alignment entry.
2. Status statements are backed by already completed plans in this file (no contradiction).
3. A clean follow-up checklist for status docs is present and actionable.

### 6) Risks and Mitigations

- **Risk:** Overwriting documentation boundaries by duplicating full status docs inside plans.
  - **Mitigation:** keep this plan focused on orchestration and evidence; retain canonical ownership in `README.md`/`PROGRESS.md`/`ROADMAP.md`.
- **Risk:** Status drift returns after future implementation passes.
  - **Mitigation:** require a documentation-sync checklist item in future phase-completion plans.

### 7) Exit Criteria (Definition of Done)

1. Documentation-sync work is explicitly represented in `PLANS.md`.
2. Current implementation reality is captured in planning context without changing doc-role structure.
3. Follow-up actions are clearly listed for authoritative status documents.

### 8) Decision Log

- **2026-03-12:** Kept documentation structure unchanged and expanded `PLANS.md` instead, because user requested retention of current documentation architecture.
- **2026-03-12:** Treated completed plans as authoritative evidence of current implementation maturity for planning updates.

### 9) Execution Checklist (Current Run)

- [x] Reviewed `README.md` documentation flow and current status wording.
- [x] Reviewed `docs/README.md` authority boundaries and lifecycle rules.
- [x] Reconciled project implementation status using completed plan entries in `PLANS.md`.
- [x] Added dedicated documentation-alignment plan entry using canonical schema.
- [x] Captured actionable follow-up synchronization targets for status documents.

### 10) Follow-up Synchronization Targets (Authoritative Docs)

1. Update `README.md` “Aktueller Stand” to reflect:
   - Phase 3 completion,
   - Phase 4 core finance write-flow audit hash-chain implementation status.
2. Update `PROGRESS.md` percentages/work packages to align with completed Phase-3 and Phase-4 slices.
3. Update `ROADMAP.md` Phase 3/4 descriptions so planned-vs-done boundaries remain accurate.

---

## PLAN-2026-03-12-PHASE4-IMPLEMENTATION — Phase 4 Audit Hash Chain (Core Finance Write Flows)

- **Status:** `completed`
- **Owner:** Codex implementation pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

- Phase 3 finance endpoints are complete and stable.
- Audit table schema (`audit_events`) already supports `event_hash` and `previous_hash`, but write flows do not yet populate it.
- Readiness documentation exists (`docs/audit-phase4-readiness.md`), including required finance hooks.

### 2) Objectives

1. Implement append-only audit hash-chain event persistence for finance create flows.
2. Emit finance audit events only after successful transaction commit.
3. Add chain integrity verification command and automated tests.

### 3) Non-Objectives

- No systemwide expansion to every domain write flow yet.
- No full operations/runbook implementation in this pass.

### 4) Workstreams and Deliverables

#### WS1 — Audit Event Writer Service

- Add canonical payload serializer and hash computation (`previous_hash`, `event_hash`).
- Persist actor/auditable metadata + payload/context snapshots.

#### WS2 — Finance Flow Integration

- Integrate post-commit event emission into:
  - `POST /api/v1/purchases`
  - `POST /api/v1/sales`
  - `POST /api/v1/valuations`
- Preserve idempotent behavior (`request_key`) and avoid duplicate events on replay responses.

#### WS3 — Chain Verification + Tests

- Add Artisan command for hash-chain continuity checks.
- Add feature tests asserting:
  - finance write flows create corresponding audit events,
  - `previous_hash` -> `event_hash` continuity,
  - integrity checker passes for valid chain.

### 5) Verification Evidence Requirements

1. `php artisan test tests/Feature/Api/FinanceApiTest.php`
2. `php artisan test` (or documented environmental warning if unrelated suites fail)
3. `vendor/bin/pint --test`
4. `vendor/bin/phpstan analyse --memory-limit=1G`
5. `php artisan audit:verify-chain`

### 6) Risks and Mitigations

- **Risk:** Hash serialization drift causes unstable hashes.
  - **Mitigation:** deterministic recursive key sorting before JSON encoding.
- **Risk:** Event written before DB commit.
  - **Mitigation:** explicit `DB::afterCommit` hooks around writer invocation.

### 7) Exit Criteria (Definition of Done)

1. Finance create flows emit append-only audit events with linked hashes.
2. Integrity command confirms valid chain on test data.
3. Tests and quality gates provide evidence.

### 8) Decision Log

- **2026-03-12:** Scope narrowed to finance write flows from readiness checklist for minimal-risk Phase 4 kickoff.

### 9) Execution Checklist (Current Run)

- [x] Create `AuditEvent` model + dedicated audit hash-chain writer service.
- [x] Wire `PurchaseController@store` post-commit audit event emission.
- [x] Wire `SaleController@store` post-commit audit event emission.
- [x] Wire `ValuationController@store` post-commit audit event emission.
- [x] Add `audit:verify-chain` Artisan command.
- [x] Add finance audit feature tests and command coverage.
- [x] Run verification commands and capture evidence.
- [x] Mark plan `completed` if all exit criteria are met.

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

---

## PLAN-2026-03-12-CI-WORKFLOW-REPAIR — GitHub Actions CI/Release Error Audit

- **Status:** `completed`
- **Owner:** Codex workflow maintenance pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

- Repository defines two workflow files: `.github/workflows/ci.yml` and `.github/workflows/release.yml`.
- `composer.json` requires PHP `^8.4`, while CI jobs were configured to run PHP `8.3`, which causes dependency installation failures.
- Release workflow configuration requires validation for obvious syntax/config issues.

### 2) Objectives

1. Audit CI and Release workflows for configuration/runtime mismatches.
2. Fix root-cause workflow errors preventing successful Actions runs.
3. Validate the updated workflow configuration with local checks where possible.

### 3) Non-Objectives

- No application runtime feature changes.
- No Docker image redesign beyond workflow correctness.

### 4) Workstreams and Deliverables

#### WS1 — Workflow Audit
- Inspect all workflow YAML files and compare runtime versions against project requirements.

**Deliverables**
- Identified root-cause error list.

#### WS2 — Workflow Corrections
- Update CI workflow runtime settings to satisfy Composer/Laravel requirements.
- Keep Release workflow untouched unless concrete errors are found.

**Deliverables**
- Updated `.github/workflows/ci.yml`.

#### WS3 — Verification
- Run local workflow-adjacent checks that exercise the corrected assumptions.
- Record evidence in this plan section.

**Deliverables**
- Command evidence log showing corrected setup compatibility.

### 5) Verification Evidence Requirements

- Confirm workflow files parse and are discoverable.
- Confirm PHP runtime requirement alignment (`composer.json` vs CI yaml).
- Run at least one local command proving PHP 8.4 expectation is now represented in workflow config.

### 6) Risks and Mitigations

- **Risk:** Additional hidden CI failures unrelated to PHP version.
  - **Mitigation:** Keep changes minimal and focused on deterministic mismatch; surface residual risks in evidence log.

### 7) Exit Criteria (Definition of Done)

1. CI workflow PHP versions align with project PHP requirements.
2. Workflow audit covers both CI and Release files.
3. Verification evidence is recorded and plan status updated to `completed`.

### 8) Decision Log

- **2026-03-12:** Prioritized fixing deterministic PHP version mismatch first, since it blocks all Composer-based CI jobs.

### 9) Execution Checklist (Current Run)

- [x] Audit `.github/workflows/ci.yml` and `.github/workflows/release.yml`.
- [x] Identify root cause(s) of likely workflow failure.
- [x] Apply minimal workflow fix(es).
- [x] Run verification commands and capture evidence.
- [x] Mark plan as completed once evidence is logged.

### 10) Verification Evidence Log (Current Run)

- `rg "php-version|\"php\"" composer.json .github/workflows/ci.yml .github/workflows/release.yml` ✅ (all CI jobs now request PHP 8.4, matching Composer constraints in `composer.json`).

---

## PLAN-2026-03-12-DOC-CURRENT-STATE — Repository-wide Documentation Cleanup

- **Status:** `completed`
- **Owner:** Codex documentation maintenance pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

- Mehrere aktive Dokumente tragen noch den Statusstempel `2026-03-06`.
- In `PLANS.md` sind mittlerweile Phase-3-Abschluss und ein Phase-4-Audit-Start bereits als `completed` belegt.
- `docs/projektstruktur.md` beschreibt derzeit überwiegend ein Zielbild, das nicht mehr der tatsächlich versionierten Verzeichnisstruktur entspricht.

### 2) Objectives

1. Alle aktiven Kern-Dokumente auf den aktuellen Projektstand synchronisieren.
2. Veraltete Status-/Phasenangaben auf Basis der vorhandenen Implementierung und Plan-Evidenz korrigieren.
3. Dokumentrollen gemäß `docs/README.md` beibehalten (kein Architekturwechsel der Dokumentation).

### 3) Non-Objectives

- Keine Code- oder API-Verhaltensänderung.
- Keine Änderung historischer Snapshot-Dokumente unter `docs/reviews/` oder Release-Notes.

### 4) Workstreams and Deliverables

#### WS1 — Baseline-Audit

- Prüfen der aktiven Root-Dokumente (`README.md`, `PROJECT_OVERVIEW.md`, `ROADMAP.md`, `PROGRESS.md`, `API_DOCS.md`, `spec.md`, `CHANGELOG.md`) gegen Implementierungsstand.
- Prüfen von `docs/projektstruktur.md` auf strukturelle Aktualität.

**Deliverables**
- Konkrete Liste der zu korrigierenden Stellen in den betroffenen Dateien.

#### WS2 — Dokumentaktualisierung

- Status- und Inhaltskorrekturen in den aktiven Dokumenten durchführen.
- Veraltete oder widersprüchliche Formulierungen bereinigen.

**Deliverables**
- Aktualisierte Dokumente mit konsistentem Standbild (2026-03-12).

#### WS3 — Verifikation

- Konsistenzprüfungen via gezielte `rg`-Checks für Datum/Phasenstatus.
- Kurzprüfung auf unstimmige Endpunkt-/Strukturangaben.

**Deliverables**
- Verifikationslog in diesem Plan inkl. ausgeführter Kommandos.

### 5) Verification Evidence Requirements

1. Keine aktiven Kern-Dokumente enthalten mehr den veralteten Stempel `Stand: 2026-03-06`.
2. `README.md`, `ROADMAP.md` und `PROGRESS.md` widersprechen nicht dem in `PLANS.md` dokumentierten Abschluss von Phase 3 und dem gestarteten Phase-4-Scope.
3. `docs/projektstruktur.md` spiegelt die real im Repo vorhandene Struktur wider.

### 6) Risks and Mitigations

- **Risk:** Überkorrektur historischer Aussagen.
  - **Mitigation:** Nur aktive Dokumente anfassen, historische Snapshot-Dateien unverändert lassen.
- **Risk:** Inkonsistente Statussprache zwischen Roadmap (Soll) und Progress (Ist).
  - **Mitigation:** Rollen strikt trennen: Roadmap = Vorwärtsblick, Progress = Live-Ist.

### 7) Exit Criteria (Definition of Done)

1. Alle betroffenen aktiven Doku-Dateien sind konsistent auf dem aktuellen Stand.
2. Plan-Evidenz enthält die ausgeführten Verifikationskommandos.
3. Änderungen sind committed und in einer PR-Zusammenfassung dokumentiert.

### 8) Decision Log

- **2026-03-12:** Dokument-Cleanup auf aktive Dateien begrenzt, um historische Evidenz nicht zu verfälschen.
- **2026-03-12:** Statuskorrekturen werden an bereits abgeschlossene Plan-Einträge gekoppelt statt an Schätzwerte.

### 9) Execution Checklist (Current Run)

- [x] Baseline-Audit der aktiven Dokumente durchgeführt.
- [x] Status-/Inhaltskorrekturen in den betroffenen Dateien umgesetzt.
- [x] Verifikationskommandos ausgeführt und Evidenz protokolliert.
- [x] Planstatus auf `completed` gesetzt.

### 10) Verification Evidence Log (Current Run)

- `git status --short` ✅ (nur geplante Dokumentdateien und `PLANS.md` geändert).
- `rg -n "Stand: 2026-03-06" README.md PROJECT_OVERVIEW.md ROADMAP.md PROGRESS.md API_DOCS.md spec.md docs/projektstruktur.md CHANGELOG.md` ✅ (keine Treffer).
- `rg -n "Phase 3|Phase 4|finance-summary|audit:verify-chain" README.md ROADMAP.md PROGRESS.md PROJECT_OVERVIEW.md API_DOCS.md` ✅ (Status- und Featureaussagen konsistent auffindbar).

---

## PLAN-2026-03-12-PHASE4-INVENTORY-AUDIT-EXPANSION — Inventory Write-Flow Audit Chain Coverage

- **Status:** `completed`
- **Owner:** Codex implementation pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

- Phase-4 hash-chain auditing is active for finance write flows (`purchases`, `sales`, `valuations`).
- Critical inventory write flows (`transfer`, `adjust-stock`) were tracked in `inventory_movements` but not yet in `audit_events`.
- Idempotent request-key handling already exists for both inventory flows and must remain duplicate-safe.

### 2) Objectives

1. Extend hash-chain audit coverage to inventory transfer/adjust write paths.
2. Preserve idempotent behavior so replayed requests do not create duplicate audit events.
3. Add feature-level verification proving event types and chain continuity.

### 3) Non-Objectives

- No expansion to catalog write/delete events in this pass.
- No changes to report endpoints (`inventory-value`, `profit-loss`) in this pass.
- No redesign of existing hash canonicalization logic.

### 4) Workstreams and Deliverables

#### WS1 — Service-level Audit Integration

- Inject `HashChainAuditLogger` into inventory services.
- Emit post-commit audit events for:
  - `inventory.transfer.executed`
  - `inventory.stock.adjusted`
- Include payload fields for actor, request key, quantities, and location deltas.

**Deliverables**
- Updated `TransferInventoryItemService` + `AdjustInventoryStockService`.
- Controller wiring to pass authenticated actor to service layer.

#### WS2 — Automated Verification Coverage

- Add feature assertions that inventory transfer + adjustment create deterministic audit events.
- Verify idempotent replay behavior keeps event cardinality stable.
- Verify audit hash-chain continuity via `audit:verify-chain` in feature test context.

**Deliverables**
- Extended `tests/Feature/Api/InventoryItemApiTest.php`.

### 5) Verification Evidence Requirements

1. Formatting/style checks pass for touched PHP files.
2. Inventory feature and service tests pass with new audit assertions.
3. Static analysis remains clean.
4. Any direct CLI `audit:verify-chain` limitation in local env is documented.

### 6) Risks and Mitigations

- **Risk:** Duplicate audit events on idempotent replay.
  - **Mitigation:** keep early-return behavior before movement creation/audit scheduling.
- **Risk:** Incorrect source location in transfer metadata.
  - **Mitigation:** capture source location from locked record before mutation and reuse in movement+audit payload.
- **Risk:** Post-commit closure reads stale target quantity.
  - **Mitigation:** fetch fresh target quantity inside closure when building audit payload.

### 7) Exit Criteria (Definition of Done)

1. Transfer/adjust API flows emit hash-chain audit events exactly once per unique request.
2. Added tests validate event types and chain links.
3. Lint/static analysis and targeted tests are green, with environment warnings explicitly recorded.

### 8) Decision Log

- **2026-03-12:** Prioritized inventory service integration over controller-only hooks to keep audit payload close to domain mutation logic.
- **2026-03-12:** Chose post-commit logging to mirror finance implementation pattern and avoid phantom audit records.

### 9) Execution Checklist (Current Run)

- [x] Add audit logger dependency + event emission in transfer service.
- [x] Add audit logger dependency + event emission in stock-adjust service.
- [x] Pass authenticated actor from inventory controller into both services.
- [x] Add feature test validating inventory audit events + chain continuity + idempotency.
- [x] Run verification commands and record evidence.
- [x] Mark plan as completed.

### 10) Verification Evidence Log (Current Run)

- `vendor/bin/pint --test app/Services/Inventory/TransferInventoryItemService.php app/Services/Inventory/AdjustInventoryStockService.php app/Http/Controllers/Api/InventoryItemController.php tests/Feature/Api/InventoryItemApiTest.php` ✅
- `php artisan test tests/Feature/Api/InventoryItemApiTest.php tests/Unit/InventoryServicesTest.php` ✅ (passes with known warning output about missing frontend asset manifest in this CLI environment)
- `vendor/bin/phpstan analyse --memory-limit=1G` ✅
- `php artisan audit:verify-chain` ⚠️ (fails outside test harness because `/workspace/tcventory/database/database.sqlite` does not exist in this environment)

## PLAN-2026-03-12-NEXT-STEPS-ORCHESTRATION — Immediate Execution Plan (Post Phase-4 Inventory Audit Expansion)

- **Status:** `in_progress`
- **Owner:** Codex planning pass
- **Last Updated:** 2026-03-12

### 1) Context Snapshot

- Core audit hash-chain support is implemented for finance write flows and inventory transfer/adjust flows.
- Uncovered mutating API endpoints still exist in catalog and inventory CRUD resources (`games`, `sets`, `products`, `inventory-items` store/update/destroy), creating an audit-gap for high-impact data changes.
- Operational hardening remains important, but the highest immediate risk is incomplete audit coverage on core mutation paths.

### 2) Objectives

1. Execute the next implementation slice as **full audit hash-chain coverage for remaining mutating catalog + inventory CRUD flows**.
2. Keep the change minimal and consistent by reusing `HashChainAuditLogger` patterns already established in finance/inventory services.
3. Define strict verification gates (tests + chain verification + static checks) for a merge-ready Phase-4 increment.

### 3) Non-Objectives

- No redesign of audit schema/hash algorithm.
- No expansion into reporting endpoints (`inventory-value`, `profit-loss`) in this slice.
- No operational stack rollout changes (Sentry/Horizon) during this implementation pass.

### 4) Workstreams and Deliverables

#### WS1 — Catalog Write-Flow Audit Coverage

- Implement audit event emission for `GameController`, `SetController`, and `ProductController` mutating actions: `store`, `update`, `destroy`.
- Standardize event naming (`catalog.game.created`, `catalog.game.updated`, etc.) and include actor/context payload parity with existing patterns.

**Deliverables**
- Controller/service updates for all catalog mutating endpoints.
- Feature tests validating event creation + hash chain continuity for catalog write flows.

#### WS2 — Inventory CRUD Audit Coverage (Non-Transfer/Adjust)

- Add audit event emission for `InventoryItemController` mutating CRUD actions already outside transfer/adjust coverage: `store`, `update`, `destroy`.
- Preserve idempotency behavior for request-key protected operations and avoid duplicate audit rows.

**Deliverables**
- Inventory CRUD audit hooks with deterministic event payloads.
- Regression tests proving existing `transfer`/`adjust-stock` audit behavior remains stable.

#### WS3 — Verification + Status Sync Completion

- Run agreed quality gates for lint, targeted API tests, static analysis, and chain verification.
- After implementation evidence is captured, update status wording in `README.md`, `PROGRESS.md`, and `ROADMAP.md` for Phase-4 scope accuracy.

**Deliverables**
- Command evidence log in this plan entry.
- Follow-up doc-sync checklist ready for closeout.

### 5) Verification Evidence Requirements

Before this plan can move from `in_progress` to `completed`:

1. Audit coverage exists for catalog + inventory CRUD mutating endpoints listed in WS1/WS2.
2. Verification command set has recorded outcomes:
   - `vendor/bin/pint --test <touched-files>`
   - `php artisan test tests/Feature/Api/GameApiTest.php tests/Feature/Api/SetApiTest.php tests/Feature/Api/ProductApiTest.php tests/Feature/Api/InventoryItemApiTest.php tests/Feature/Api/FinanceApiTest.php`
   - `vendor/bin/phpstan analyse --memory-limit=1G`
   - `php artisan audit:verify-chain` (or documented environment limitation)
3. Existing finance + inventory transfer/adjust chain assertions continue to pass.

### 6) Risks and Mitigations

- **Risk:** Event taxonomy fragmentation across controllers.
  - **Mitigation:** define a consistent naming convention before coding and assert exact types in feature tests.
- **Risk:** Duplicate or missing events around delete/update edge cases.
  - **Mitigation:** use post-commit logging pattern already proven in finance/inventory services and test create/update/destroy triplets.
- **Risk:** Chain verification command is environment-sensitive (missing sqlite file).
  - **Mitigation:** run command where possible and explicitly log the known environment constraint when it cannot execute.

### 7) Exit Criteria (Definition of Done)

1. Catalog + inventory CRUD write paths emit hash-chain audit events with actor/context payloads.
2. Targeted API tests validate event types, chain links, and no regressions in existing audited flows.
3. Quality gates and chain verification evidence are recorded, and status docs have an explicit sync checklist.

### 8) Decision Log

- **2026-03-12:** Selected WS1+WS2 (remaining mutating CRUD audit coverage) as the immediate next phase because it closes the largest compliance gap with minimal architecture churn.
- **2026-03-12:** Deferred operations hardening depth to the following slice once write-flow audit completeness is achieved.

### 9) Execution Checklist (Current Run)

- [x] Reviewed latest completed plan entries for current-state grounding.
- [x] Confirmed uncovered mutating endpoints from `routes/api.php` and current audit-enabled controllers/services.
- [x] Selected next implementation slice: catalog + inventory CRUD audit coverage.
- [x] Flipped plan status to `in_progress` with explicit verification command set.
- [x] Implement WS1 catalog audit hooks and tests.
- [x] Implement WS2 inventory CRUD audit hooks and regression tests.
- [x] Run WS3 verification gates and capture evidence.
- [ ] Execute status-doc sync checklist after implementation proof.

### 10) Verification Evidence (Implementation Run)

- `vendor/bin/pint --test app/Http/Controllers/Api/GameController.php app/Http/Controllers/Api/SetController.php app/Http/Controllers/Api/ProductController.php app/Http/Controllers/Api/InventoryItemController.php tests/Feature/Api/GameApiTest.php tests/Feature/Api/SetApiTest.php tests/Feature/Api/ProductApiTest.php tests/Feature/Api/InventoryItemApiTest.php` ✅
- `php artisan test tests/Feature/Api/GameApiTest.php tests/Feature/Api/SetApiTest.php tests/Feature/Api/ProductApiTest.php tests/Feature/Api/InventoryItemApiTest.php tests/Feature/Api/FinanceApiTest.php` ✅ (all assertions pass; framework emits non-failing warning output from existing file fixture reads)
- `vendor/bin/phpstan analyse --memory-limit=1G` ✅
- `php artisan audit:verify-chain` ⚠️ fails in this environment because `/workspace/tcventory/database/database.sqlite` does not exist.

### 10) Selected Scope Evidence (Planning Pass)

- Mutating endpoints inventory from `routes/api.php` confirms catalog/inventory CRUD write surfaces remain in scope (`games`, `sets`, `products`, `inventory-items` with store/update/destroy).
- Existing audit integration currently appears in finance controllers and inventory transfer/adjust services, establishing reuse baseline for the next implementation slice.
