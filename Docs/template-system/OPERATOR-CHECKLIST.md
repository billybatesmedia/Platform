# Lithia V1 Operator Checklist

Use this checklist for the internal-assisted V1 workflow.

Reference:

- V1 pass/fail gate: `Docs/template-system/V1-DEFINITION-OF-DONE.md`

## Intake

1. Open `Appearance > Launch Wizard` or `Appearance > Project Manager`.
2. Fill or update the structured project fields.
3. Confirm the payload has:
   - `project.site_key`
   - `project.industry`
   - business brand name
   - at least one offer when the site should have service pages

## Approve

1. In `Appearance > Project Manager`, review the validation panel.
2. Fix any validation errors before import.
3. Set `Payload Review State` to `approved`.
4. Keep the site review state at `intake` until the import is actually applied.

## Backup

1. Download the current payload JSON from Project Manager.
2. Export a database backup before any live import:

```bash
wp db export Docs/template-system/backups/$(date +%Y%m%d-%H%M%S)-before-import.sql
```

3. If you are testing a risky change, use `Dry Run Import` first.

## Import

1. Run `Dry Run Import`.
2. Review:
   - pages summary
   - page seeds summary
   - offers summary
   - providers summary
   - preserved fields
   - validation warnings
3. Run `Apply Import`.
4. Set the site review state to `imported`.

## New Site Creation Cycle (Full Pass)

Use this sequence when validating a brand-new site creation cycle end-to-end:

1. Complete intake fields in Launch Wizard or Project Manager.
2. Confirm payload validation is clean and set payload review state to `approved`.
3. Run dry-run import and review warnings.
4. Apply import.
5. Confirm site review state is `imported` immediately after apply.
6. Complete manual QA/copy polish and move to `qa`.
7. Move to `launched` before running final release readiness suite.

Important: release suites expect `review_state = launched`. Running release checks while still `imported` will fail launch-state gates.

## QA

1. Review the homepage, About, Contact, booking page, and service pages.
2. Confirm:
   - homepage is not blank and renders front-page sections from `templates/front-page.html`
   - titles and excerpts imported correctly
   - Rank Math fields look sane
   - CTAs point to the right pages
   - no seed or internal admin copy is visible
   - header/footer render correctly
   - no PHP session cookie is being set on public pages
   - accessibility issues did not regress
3. Move the site review state to `qa`.

## V1 Done Gate

1. Run the full V1 Definition Of Done gate:
   - `Docs/template-system/V1-DEFINITION-OF-DONE.md`
2. Confirm all P0 gates pass:
   - import stability
   - schema coverage
   - QA release checks
   - rollback readiness
   - regression test baseline
3. Save evidence artifacts for this candidate:
   - import run summary
   - payload samples used
   - QA signoff notes
   - rollback verification note
   - test run summary
4. Run the importer regression runner and save output:

```bash
wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-importer-regression.php --path=/Users/billybates/Local_Sites/service-site/app/public
```

5. Run the schema-focused importer regression runner and save output:

```bash
wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-importer-schema-regression.php --path=/Users/billybates/Local_Sites/service-site/app/public
```

6. Run the one-command regression suite and save aggregate output:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-importer-regression-suite.sh /Users/billybates/Local_Sites/service-site/app/public
```

7. Run the V1 gate check audit and save output:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-v1-gate-check.sh /Users/billybates/Local_Sites/service-site/app/public
```

8. Do not move to `launched` until this gate is complete.

## Launch

1. Finish the manual copy and SEO polish.
2. Run a final front-end check.
3. Confirm the `V1 Done Gate` section above has passed.
4. Move the site review state to `launched`.

## Site Docs (/site-docs/) Pass

1. Verify baseline docs are published in `Site Docs`.
2. Verify `/site-docs/` archive and single docs render correctly.
3. Confirm doc type/audience taxonomy assignments are correct.
4. Update runbook references if any workflow changed:
   - `Docs/template-system/SITE-DOCS-RUNBOOK.md`

## Deployment State Tracking (Phase 4 Baseline)

1. Run the deployment state tracker:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-deployment-state-check.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4a-deployment-state-check.json`
3. Confirm:
   - `review_state` is `launched`
   - `gate.v1_ready` is `true`
   - `pass` is `true`

## QA Checklist Audit (Phase 4 Baseline)

1. Run the QA checklist audit:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-qa-checklist-audit.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4b-qa-checklist-audit.json`
3. Confirm:
   - `totals.failed` is `0`
   - `pass` is `true`

## Change Log Audit (Phase 4 Baseline)

1. Run the change-log audit:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-change-log-audit.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4c-change-log-audit.json`
3. Confirm:
   - `totals.missing` is `0`
   - `change_log.review_state` is `launched`
   - `pass` is `true`

## Re-sync Readiness Audit (Phase 4 Baseline)

1. Run the re-sync readiness audit:

```bash
LITHIA_RESYNC_PAYLOAD_FILE=/Users/billybates/Local_Sites/service-site/app/public/Docs/template-system/evidence/item-2h-offer-alias-payload.json \
  bash wp-content/themes/lithia-web-service-theme/tests/run-resync-readiness-check.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4d-resync-readiness-check.json`
3. Confirm:
   - `resync_readiness.dry_run_success` is `true`
   - `resync_readiness.validation_errors_zero` is `true`
   - `lock_guard_regression.pass` is `true`
   - `pass` is `true`

## Phase 4 Orchestration Suite

1. Run the one-command Phase 4 suite:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-phase4-orchestration-suite.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4e-phase4-orchestration-suite.json`
3. Confirm:
   - `totals.failed` is `0`
   - all `checks[].pass` are `true`
   - `pass` is `true`

## Release Readiness Suite

1. Run the one-command release readiness suite:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-readiness-suite.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4f-release-readiness-suite.json`
3. Confirm:
   - `totals.failed` is `0`
   - all `checks[].pass` are `true`
   - `pass` is `true`

## Evidence Manifest Audit

1. Run the evidence manifest audit:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-evidence-manifest-audit.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4g-evidence-manifest-audit.json`
3. Confirm:
   - `totals.missing` is `0`
   - `pass` is `true`

## Final Handoff Record

1. Run the final handoff record command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-handoff-record.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4h-release-handoff-record.json`
3. Confirm:
   - `checks.release_suite_pass` is `true`
   - `checks.evidence_manifest_pass` is `true`
   - `checks.live_review_state_launched` is `true`
   - `pass` is `true`

## Final Certification Suite

1. Run the final certification suite:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-final-certification-suite.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4i-final-certification-suite.json`
3. Confirm:
   - `totals.failed` is `0`
   - all `checks[].pass` are `true`
   - `pass` is `true`

## V1 Completion Certificate

1. Run the V1 completion certificate command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-v1-completion-certificate.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4j-v1-completion-certificate.json`
3. Confirm:
   - `checks.final_certification_pass` is `true`
   - `checks.review_state_launched` is `true`
   - `v1_complete` is `true`

## Release Packet Export

1. Run the release packet export:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-packet-export.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4k-release-packet-export.json`
3. Confirm:
   - `totals.missing` is `0`
   - `pass` is `true`

## Release Packet Verify

1. Run the release packet verify command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-packet-verify.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4l-release-packet-verify.json`
3. Confirm:
   - `totals.missing` is `0`
   - `totals.checksum_mismatch` is `0`
   - `pass` is `true`

## Release Timeline Audit

1. Run the release timeline audit command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-timeline-audit.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save output to:
   - `Docs/template-system/evidence/item-4m-release-timeline-audit.json`
3. Confirm:
   - `totals.missing` is `0`
   - `totals.missing_timestamp` is `0`
   - `totals.failing` is `0`
   - `pass` is `true`

## Release Closeout Report

1. Run the release closeout report command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-closeout-report.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Save outputs to:
   - `Docs/template-system/evidence/item-4n-release-closeout-report.json`
   - `Docs/template-system/evidence/item-4n-release-closeout-report.md`
3. Confirm:
   - `pass` is `true`
   - report status is `COMPLETE`

## Full Release Sequence Runner

1. Run the one-command full release sequence:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release.sh /Users/billybates/Local_Sites/service-site/app/public
```

2. Confirm:
   - `Docs/template-system/evidence/item-4o-full-release-sequence-summary.md` exists
   - `Docs/template-system/evidence/item-4p-evidence-integrity-snapshot-summary.md` exists
   - `Docs/template-system/evidence/item-4q-doc-consistency-audit-summary.md` exists
   - `Docs/template-system/evidence/item-4r-release-status-board-summary.md` exists
   - `Docs/template-system/evidence/item-4s-release-artifact-index-summary.md` exists
   - `Docs/template-system/evidence/item-4t-release-attestation-summary.md` exists
   - `item-4o-full-release-sequence.json` has `totals.failed` equal to `0`
   - `item-4s-release-artifact-index.json` has `totals.failing` equal to `0`
   - `pass` is `true`

## Evidence Integrity Snapshot

1. Run the evidence integrity snapshot command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-evidence-integrity-snapshot.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4p-evidence-integrity-snapshot.json
```

2. Confirm:
   - `totals.hash_failures` is `0`
   - `anchors.full_release_sequence_pass` is `true`
   - `pass` is `true`

## Doc Consistency Audit

1. Run the documentation consistency audit command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-doc-consistency-audit.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4q-doc-consistency-audit.json
```

2. Confirm:
   - `totals.failed` is `0`
   - `pass` is `true`

## Release Status Board

1. Run the release status board command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-status-board.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4r-release-status-board.json
```

2. Confirm:
   - `checks.full_release_sequence_pass` is `true`
   - `checks.evidence_integrity_pass` is `true`
   - `checks.doc_consistency_pass` is `true`
   - `pass` is `true`

## Release Artifact Index

1. Run the release artifact index command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-artifact-index.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4s-release-artifact-index.json
```

2. Confirm:
   - `totals.missing` is `0`
   - `totals.failing` is `0`
   - `checks.evidence_index_candidate_item4` is `true`
   - `pass` is `true`

## Release Attestation

1. Run the release attestation command:

```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release-attestation.sh /Users/billybates/Local_Sites/service-site/app/public > Docs/template-system/evidence/item-4t-release-attestation.json
```

2. Confirm:
   - `checks.pass_4o` is `true`
   - `checks.pass_4p` is `true`
   - `checks.pass_4q` is `true`
   - `checks.pass_4r` is `true`
   - `checks.pass_4s` is `true`
   - `checks.evidence_index_candidate_4t` is `true`
   - `pass` is `true`

## Lock

1. In `Appearance > Project Manager`, open `Managed Content Locks`.
2. Lock any page, service, or provider that has been manually refined and should not be overwritten.
3. Re-run a dry run after locking if more imports are expected.

## Rollback

Use the least-destructive rollback that fits the issue:

1. Restore an older payload from `Import History` if the draft payload changed incorrectly.
2. Re-import an older payload with `force` only when you mean to overwrite managed fields.
3. Restore the database backup if the site content itself needs to be fully rolled back.
