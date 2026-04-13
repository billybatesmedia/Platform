# Site Docs Runbook

This runbook defines how to create and maintain docs in the site-level `/site-docs/` library.

## Where Site Docs Live

- Post type: `site_docs`
- Archive URL: `/site-docs/`
- Taxonomies:
  - `site_doc_type` (`How To`, `Build Notes`, `Tech Notes`)
  - `site_doc_audience` (`End Client`, `Admin`)

Implementation source:

- `wp-content/themes/lithia-web-service-theme/inc/site-docs.php`

## Required Baseline Docs for V1

Create at least these docs before handoff:

1. `How to edit homepage content` (`How To`, `End Client`)
2. `How to update services and pricing` (`How To`, `End Client`)
3. `How to update booking/contact settings` (`How To`, `Admin`)
4. `Launch and rollback notes` (`Build Notes`, `Admin`)
5. `Importer + payload contract` (`Tech Notes`, `Admin`)

## Authoring Rules

1. Use plain language and step-by-step actions.
2. Put client-safe instructions in `End Client`; internal details in `Admin`.
3. Include exact admin menu paths (example: `Appearance > Project Manager`).
4. Include links to canonical runbooks in `Docs/template-system/` where relevant.
5. Keep screenshots optional; content must still be usable without them.

## Publish Workflow

1. In WordPress admin, open `Site Docs > Add New`.
2. Add title, body, short excerpt, and (optional) featured image.
3. Assign one `Doc Type` and one or more `Audience` terms.
4. Publish and verify it appears on `/site-docs/`.
5. Click into the single doc page and verify layout/readability.
6. Add or update cross-links in docs when a process changes.

## Update Workflow During Releases

1. After importer/workflow changes, update affected docs in `/site-docs/`.
2. Update corresponding source docs under `Docs/template-system/`.
3. Run release checks before final launch signoff.
4. Commit doc updates with a focused message and push.

## Quality Gate

Before marking release docs complete:

1. `/site-docs/` archive loads with no blank cards.
2. Every baseline V1 doc exists and is published.
3. Taxonomy filters/labels are correct.
4. Instructions match current importer behavior and review states.
