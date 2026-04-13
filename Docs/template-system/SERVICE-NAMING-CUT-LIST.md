# Service Naming Migration Cut List

Date: 2026-04-12

Goal: remove service-business-specific compatibility naming (`offer_*` aliases and mirrored meta) while keeping canonical `service_*` fields.

## 1) Read-path alias fallbacks to remove

1. `inc/project-importer.php`
- `lithia_project_import_normalize_offer_payload()` currently reads many `offer_*` aliases before/alongside canonical keys.
- Primary lines: 1141-1267.

2. `inc/project-admin.php`
- `lithia_project_admin_get_offer_editor_rows()` currently reads `offer_*` fallbacks for summary, CTA, delivery/timeline, pricing, audience/outcomes, spotlight, and providers.
- Primary lines: 223-288.

## 2) Write-path mirrored keys to remove

1. `inc/project-importer.php`
- `lithia_project_import_offers()` writes both canonical `service_*` and mirrored `offer_*` meta for the same values.
- Primary lines: 1877-1927.
- This is the core compatibility layer to remove after read-path cleanup and migration.

## 3) Admin payload naming to rename

1. `inc/project-admin.php`
- Request/form arrays still use `offer_*` field names for payload editor input.
- Primary lines: 419-490 and render table fields around 1670+.
- Rename request keys and input names to canonical naming to stop introducing new alias-shaped payloads.

## 4) Test suite dependencies on alias behavior

1. `tests/run-importer-regression.php`
- Explicit alias mapping test fixtures (`offer_primary_cta_label`, `offer_delivery_mode`, etc.).
- Primary lines: 53-80.

2. `tests/run-importer-schema-regression.php`
- Seeds alias fields and asserts alias mapping behavior.
- Primary lines: 43-49 and 96-119.

3. `tests/run-phase4-orchestration-suite.sh`
- Default fallback payload points to `item-2h-offer-alias-payload.json`.
- Primary line: 8.

4. `tests/run-full-release-sequence.sh`
- Default fallback payload points to `item-2h-offer-alias-payload.json`.
- Primary line: 9.

5. `tests/run-resync-readiness-check.sh`
- Prefers `item-2h-offer-alias-payload.json` as first payload candidate.
- Primary lines: 13-14.

## 5) Sample payload and naming labels to update

1. `inc/project-admin.php`
- Sample key/label/path still reference `service-business-v1-starter`.
- Primary lines: 64-67 and default selection line 951.

2. `tests/run-importer-regression.php`
3. `tests/run-importer-schema-regression.php`
- Both currently load `Docs/template-system/sample-payloads/service-business-v1-starter.json`.

## 6) Docs status location to flip after removal

1. `Docs/template-system/README.md`
- Remaining-gap statement currently says alias compatibility is still active.
- Primary lines: 545-547.

## Suggested execution order

1. Update importer read path to canonical-only (temporarily keep mirrored write path).
2. Update admin payload editor to canonical naming.
3. Add one-time migration script to backfill historical `offer_*` data into `service_*` where missing.
4. Remove mirrored `offer_*` meta writes from importer.
5. Rewrite regression and release-runner references from alias fixtures to canonical fixtures.
6. Update docs gap status and re-run full release suite.
