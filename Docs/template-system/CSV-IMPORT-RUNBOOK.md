# CSV Import Runbook (V1)

This runbook is for testing new site creation from one or several CSV files.

Use this in V1 when intake data starts in spreadsheets.

## Important Model Decision

- Canonical source of truth remains the JSON payload used by `Project Manager`.
- CSV is an input format for assembling that payload.
- Do not treat direct post/meta CSV imports as the primary release path.

## CSV Pack Layout

Use these files together:

- `Docs/template-system/sample-csv/project.csv`
- `Docs/template-system/sample-csv/business.csv`
- `Docs/template-system/sample-csv/location.csv`
- `Docs/template-system/sample-csv/seo.csv`
- `Docs/template-system/sample-csv/booking.csv`
- `Docs/template-system/sample-csv/providers.csv`
- `Docs/template-system/sample-csv/offers.csv`
- `Docs/template-system/sample-csv/pages.csv`
- `Docs/template-system/sample-csv/faq.csv`
- `Docs/template-system/sample-csv/proof.csv`

Reference mapping table:

- `Docs/template-system/CSV-PAYLOAD-MAPPING.md`

## Multi-Value Conventions

Use pipe-delimited values (`|`) in one cell for lists.

Examples:

- `service_area`: `Portland|Beaverton|Lake Oswego`
- `provider_slugs`: `billy|alex`
- `service_audience`: `small businesses|founders`

## New Site Creation Cycle (CSV -> Payload -> Import)

1. Fill the CSV templates.
2. Validate required columns using the mapping table.
3. Assemble the canonical JSON payload from the CSV values.
4. In WordPress admin open `Appearance > Project Manager`.
5. Paste/update payload in the raw JSON editor.
6. Save payload draft and clear validation errors.
7. Set payload review state to `approved`.
8. Run `Dry Run Import`.
9. Review summary and warnings.
10. Run `Apply Import`.
11. Confirm site review state is `imported`.
12. Perform manual QA and move to `qa`.
13. Move to `launched` before release readiness suites.

Important: final release checks expect `review_state = launched`.

## Several CSV Upload Test Strategy

When testing multiple CSV revisions for the same site:

1. Keep `project.site_key` stable across revisions.
2. Keep each `record_key` stable in `offers`, `providers`, and `pages`.
3. Run revision A as baseline import.
4. Apply revision B with changed rows only.
5. Verify expected updates and no accidental post duplication.
6. Use import history snapshots for rollback if needed.

## Optional WP All Import Path (Advanced)

WP All Import Pro is installed and can import CSV directly to post types.

Use only for controlled advanced tests because this bypasses parts of the canonical payload flow.

Recommended direct targets:

- `providers.csv` -> `providers` post type
- `offers.csv` -> `services` post type
- `pages.csv` -> `page` post type (careful with homepage/front page routing)

If you use this path:

1. Match existing records by stable unique key (`record_key` meta or slug).
2. Map only canonical `service_*` meta keys.
3. Avoid writing legacy `offer_*` keys.
4. Re-run Project Manager dry run to confirm parity.

## Exit Criteria for CSV Test Pass

1. Payload validates with zero blocking errors.
2. Dry run succeeds.
3. Apply import succeeds.
4. Service/provider/page counts match expected rows.
5. No duplicate records from repeated imports.
6. `/site-docs/` and core pages still render correctly.
