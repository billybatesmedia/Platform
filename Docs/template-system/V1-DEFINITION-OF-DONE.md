# Lithia V1 Definition Of Done

This document defines the minimum release bar for calling the template system a "good V1".

If any P0 gate fails, V1 is not done.

## Exit Rule

V1 is done only when all of the following are true:

1. All P0 gates pass.
2. No unresolved critical errors exist in the import path.
3. Required evidence artifacts are captured and stored in the project docs or ticket.

## P0 Gate 1: Import Stability

Goal: the importer is predictable in real usage.

Pass criteria:

1. Run 5 consecutive end-to-end cycles with no fatal errors:
   - `Dry Run Import`
   - `Apply Import`
2. Use at least 3 payload profiles during those runs:
   - minimal payload
   - typical payload
   - larger payload with multiple offers/providers/faqs
3. Both admin UI path and WP-CLI path execute against the same importer behavior.
4. No unresolved PHP fatal errors in the relevant run window logs.

Fail examples:

1. Any runtime fatal during import.
2. UI succeeds but CLI behavior diverges.
3. Repeated non-deterministic import results on identical payloads.

## P0 Gate 2: Canonical Schema Coverage

Goal: V1 handles minimum cross-industry payload fields without manual hacks.

Required modeled fields (minimum):

1. `offers[].price_from`
2. `offers[].price_notes`
3. `offers[].audience[]`
4. `offers[].outcomes[]`
5. `faq[]` as first-class payload records
6. testimonial/proof data (`proof.testimonials[]` or equivalent canonical section)
7. review state handling (`intake`, `approved`, `imported`, `qa`, `launched`)
8. page-level seed records for core pages (`pages[]` with stable `record_key` values)

Pass criteria:

1. Each required field can be:
   - accepted by payload validation
   - imported
   - surfaced in expected site/admin output
2. Missing optional fields do not break import.
3. Schema version is explicit and stored (`schema_version`).

Fail examples:

1. Field accepted but silently dropped.
2. Field import requires one-off manual DB/meta edits.
3. Schema changes ship without version updates.

## P0 Gate 3: QA Release Gate

Goal: every generated site meets a baseline quality bar before launch.

Pass criteria:

1. QA checklist is completed for core pages:
   - Home
   - About
   - Contact
   - Booking (if enabled)
   - Service/offer pages
2. Content/SEO sanity confirmed:
   - titles/excerpts are sensible
   - Rank Math baseline fields are populated where expected
   - no seed/admin/internal placeholder copy is publicly visible
3. UX/technical checks pass:
   - static front page renders non-empty homepage sections (not a blank `post-content` shell)
   - block theme includes and uses `templates/front-page.html` for homepage layout
   - CTA targets are valid
   - header/footer render correctly
   - no unexpected PHP session cookie on public pages
   - no accessibility regressions in a basic smoke pass

Fail examples:

1. Broken CTA or dead-end conversion flow.
2. Internal seed copy visible on public pages.
3. Regressed accessibility issue introduced by import/template changes.

## P0 Gate 4: Safety And Rollback

Goal: operators can safely recover from bad imports.

Pass criteria:

1. Pre-import backup procedure is documented and used.
2. Import History has usable snapshots.
3. At least one rollback path is verified end-to-end:
   - payload-level restore, or
   - DB restore for full rollback
4. Managed content locks prevent unintended overwrite of refined content.

Fail examples:

1. No recoverable backup before apply import.
2. Locks exist but still allow overwrite of locked records.

## P0 Gate 5: Regression Test Baseline

Goal: prevent repeat breakage while iterating.

Pass criteria:

1. A minimum automated test set exists for importer-critical behavior:
   - normalization/sanitization
   - validation outcomes
   - managed overwrite/lock decisions
   - review state guards
2. Tests run before release candidate signoff.
3. Regression runner command is documented and repeatable for operators.
4. Canonical schema regression checks exist for `pages[]`, `faq[]`, `proof`, and `offer_*` alias support.
5. A one-command suite exists that runs both importer and schema regression runners.
6. A one-command V1 gate audit exists and reports aggregate P0 evidence status.

Suggested runner command:

```bash
wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-importer-regression.php --path=/Users/billybates/Local_Sites/service-site/app/public
```

Suggested schema runner command:

```bash
wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-importer-schema-regression.php --path=/Users/billybates/Local_Sites/service-site/app/public
```

Suggested one-command suite:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-importer-regression-suite.sh /Users/billybates/Local_Sites/service-site/app/public
```

Suggested V1 gate audit:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-v1-gate-check.sh /Users/billybates/Local_Sites/service-site/app/public
```

Recommended initial floor:

1. 10 to 20 focused tests around the canonical importer and state transitions.

Fail examples:

1. Import logic changes with zero automated verification.
2. Known regression reappears because prior behavior was untested.

## Required Evidence Artifacts

Capture these for each V1 release candidate:

1. Import stability evidence:
   - `item-1a-import-stability-report.md`
   - `item-1b-profile-coverage-report.md`
   - `item-1c-ui-cli-parity-report.md`
   - `item-1d-critical-error-disposition.md`
2. Canonical schema coverage evidence:
   - `item-2a-price-from-coverage-report.md`
   - `item-2b-price-notes-coverage-report.md`
   - `item-2c-audience-coverage-report.md`
   - `item-2d-outcomes-coverage-report.md`
   - `item-2e-faq-coverage-report.md`
   - `item-2f-proof-testimonials-coverage-report.md`
   - `item-2g-page-seeds-coverage-report.md`
   - `item-2h-generic-offers-coverage-report.md`
3. Regression and gate evidence:
   - `item-3a-regression-test-summary.md`
   - `item-3b-schema-regression-summary.md`
   - `item-3c-regression-suite-summary.md`
   - `item-3d-v1-gate-check-summary.md`
   - `item-3d-qa-signoff-notes.md`
   - `item-3d-rollback-verification-note.md`
   - `item-3e-release-candidate-handoff.md`
   - `item-3f-launch-state-transition.md`
4. Phase 4 release operations evidence:
   - `item-4a-deployment-state-summary.md`
   - `item-4b-qa-checklist-summary.md`
   - `item-4c-change-log-summary.md`
   - `item-4d-resync-readiness-summary.md`
   - `item-4e-phase4-orchestration-suite-summary.md`
   - `item-4f-release-readiness-suite-summary.md`
   - `item-4g-evidence-manifest-summary.md`
   - `item-4h-release-handoff-summary.md`
   - `item-4i-final-certification-summary.md`
   - `item-4j-v1-completion-summary.md`
   - `item-4k-release-packet-summary.md`
   - `item-4l-release-packet-verify-summary.md`
   - `item-4m-release-timeline-summary.md`
   - `item-4n-release-closeout-report.md`
   - `item-4o-full-release-sequence-summary.md`
   - `item-4p-evidence-integrity-snapshot-summary.md`
   - `item-4q-doc-consistency-audit-summary.md`
   - `item-4r-release-status-board-summary.md`
   - `item-4s-release-artifact-index-summary.md`
   - `item-4t-release-attestation-summary.md`
5. Evidence index:
   - `Docs/template-system/evidence/README.md` reflects the current candidate and all artifacts above.

## Canonical Release Command Order

Use this order for release execution and evidence capture:

1. `bash wp-content/themes/lithia-web-service-theme/tests/run-importer-regression-suite.sh /Users/billybates/Local_Sites/service-site/app/public`
2. `bash wp-content/themes/lithia-web-service-theme/tests/run-v1-gate-check.sh /Users/billybates/Local_Sites/service-site/app/public`
3. `bash wp-content/themes/lithia-web-service-theme/tests/run-deployment-state-check.sh /Users/billybates/Local_Sites/service-site/app/public`
4. `bash wp-content/themes/lithia-web-service-theme/tests/run-qa-checklist-audit.sh /Users/billybates/Local_Sites/service-site/app/public`
5. `bash wp-content/themes/lithia-web-service-theme/tests/run-change-log-audit.sh /Users/billybates/Local_Sites/service-site/app/public`
6. `bash wp-content/themes/lithia-web-service-theme/tests/run-resync-readiness-check.sh /Users/billybates/Local_Sites/service-site/app/public`
7. `bash wp-content/themes/lithia-web-service-theme/tests/run-phase4-orchestration-suite.sh /Users/billybates/Local_Sites/service-site/app/public`
8. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-readiness-suite.sh /Users/billybates/Local_Sites/service-site/app/public`
9. `bash wp-content/themes/lithia-web-service-theme/tests/run-evidence-manifest-audit.sh /Users/billybates/Local_Sites/service-site/app/public`
10. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-handoff-record.sh /Users/billybates/Local_Sites/service-site/app/public`
11. `bash wp-content/themes/lithia-web-service-theme/tests/run-final-certification-suite.sh /Users/billybates/Local_Sites/service-site/app/public`
12. `bash wp-content/themes/lithia-web-service-theme/tests/run-v1-completion-certificate.sh /Users/billybates/Local_Sites/service-site/app/public`
13. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-packet-export.sh /Users/billybates/Local_Sites/service-site/app/public`
14. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-packet-verify.sh /Users/billybates/Local_Sites/service-site/app/public`
15. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-timeline-audit.sh /Users/billybates/Local_Sites/service-site/app/public`
16. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-closeout-report.sh /Users/billybates/Local_Sites/service-site/app/public`
17. `bash wp-content/themes/lithia-web-service-theme/tests/run-evidence-integrity-snapshot.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4p-evidence-integrity-snapshot.json`
18. `bash wp-content/themes/lithia-web-service-theme/tests/run-doc-consistency-audit.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4q-doc-consistency-audit.json`
19. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-status-board.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4r-release-status-board.json`
20. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-artifact-index.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4s-release-artifact-index.json`
21. `bash wp-content/themes/lithia-web-service-theme/tests/run-release-attestation.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4t-release-attestation.json`

Optional one-command wrapper:

22. `bash wp-content/themes/lithia-web-service-theme/tests/run-release.sh /Users/billybates/Local_Sites/service-site/app/public`

## Decision

Use this final rule:

1. If all P0 gates pass and evidence is recorded, mark V1 as done.
2. If any P0 gate fails, keep review state at `qa` or earlier and do not claim V1 complete.
