# Lithia Template System

This document is the working reference for turning the current Lithia Web site structure into a scalable template product.

The long-term model is:

1. A client completes a structured intake in a portal.
2. The portal stores a canonical seed payload.
3. A target WordPress site imports that payload.
4. The importer creates or updates core pages, services, SEO fields, and site settings.
5. A human editing pass refines the generated draft before launch.

The current V1 operating model can run fully inside the WordPress backend:

1. Use the Launch Wizard to seed the project.
2. Manage the structured fields and canonical JSON payload in the Project Manager admin page.
3. Run dry runs and approved imports from the same payload.
4. Lock manually edited records when needed.
5. Move the site through review states until launch.

The important distinction is:

- Seed data = facts.
- Generated content = first-pass draft copy and SEO built from those facts.

Do not ask the client to write final website copy in the first round.

## V1 Definition

V1 means:

- one template
- one vertical-friendly schema
- internal-assisted launch
- JSON as the source of truth
- one importer entry point
- explicit review states
- conservative marketing language

V1 does not mean fully self-serve provisioning or fully finished copy with no human review.

V1 release readiness is gated by a strict pass/fail checklist:

- `Docs/template-system/V1-DEFINITION-OF-DONE.md`

## Product Claim

The defensible product claim is:

`Lithia Web uses a structured intake and template system to turn client business data into a draft WordPress site with core pages, service pages, and baseline SEO.`

Avoid claiming that the system fully replaces final strategy, writing, or launch QA.

## Why Both Are Needed

Yes, both layers are needed:

- Portal intake spec: defines what the client enters.
- Canonical seed schema: defines what the system stores and sends to the site generator.

The intake spec is UX.
The seed schema is system architecture.

If those are not separated, the system becomes brittle.

## Recommended Architecture

Use a portal-first architecture long term.

1. The client portal hosts the intake wizard.
2. The portal stores normalized project data as JSON.
3. The template site receives a JSON payload and imports it.
4. CSV is optional for exports and reporting, not the primary transport format.

Why JSON should be primary:

- The data model is nested.
- Services or offers have repeaters and relationships.
- SEO, FAQs, media, and page drafts do not fit cleanly into flat CSV rows.
- The current theme code already leans toward structured payloads and stable record keys.

For V1, the backend UI in WordPress is an acceptable substitute for the external portal as long as the payload contract stays canonical and JSON-first.

## Current Implementation Direction

The current site-side implementation should center on one entry point:

- `lithia_import_project_payload( array $payload, array $args = [] ): array`

That importer should be the path used by:

- the local Launch Wizard
- WP-CLI imports
- future portal-driven imports
- any future internal automation

As of this V1 direction, CSV should be treated as a convenience export or spreadsheet workflow, not the canonical project source.

## Current V1 Backend UI

The current internal workflow should use these WordPress admin surfaces:

- `Appearance > Launch Wizard`
- `Appearance > Project Manager`

Use them like this:

1. Seed or update the draft via Launch Wizard.
2. Rebuild the canonical payload from that state in Project Manager.
3. Use the structured Project Manager sections for Business, Offers, SEO, Booking, and FAQ.
4. Edit the raw payload JSON only when the structured fields are not enough.
5. Download the payload JSON before risky changes and use Import History snapshots as payload-level rollback points.
6. Run a dry run import.
7. Apply the import once the payload review state is `approved`.
8. Mark the site review state as `imported`, `qa`, or `launched`.
9. Lock records that have been manually refined and should no longer be importer-managed.

Supporting docs:

- Operator checklist: `Docs/template-system/OPERATOR-CHECKLIST.md`
- V1 done criteria: `Docs/template-system/V1-DEFINITION-OF-DONE.md`
- Starter payload: `Docs/template-system/sample-payloads/service-business-v1-starter.json`
- Site docs runbook (`/site-docs/`): `Docs/template-system/SITE-DOCS-RUNBOOK.md`

This keeps V1 internal-assisted and avoids pretending the system is already a full client-facing portal.

## Site Docs Library

Client/admin documentation is published through the `site_docs` content model and rendered at:

- `/site-docs/` (archive)
- `/site-docs/<doc-slug>/` (single doc)

Theme implementation references:

- `wp-content/themes/lithia-web-service-theme/inc/site-docs.php`
- `wp-content/themes/lithia-web-service-theme/templates/archive-site_docs.html`
- `wp-content/themes/lithia-web-service-theme/templates/single-site_docs.html`

Use the runbook for required categories, audience tags, and publishing workflow:

- `Docs/template-system/SITE-DOCS-RUNBOOK.md`

## Intake Design Principles

The intake wizard should collect facts in stages.

Recommended stage order:

1. Business basics
2. Location and delivery model
3. Offers or services
4. Pricing and durations
5. Audience
6. Trust and credentials
7. Booking or lead flow
8. FAQs
9. Media
10. SEO targets

That order matters because later defaults can be derived from earlier answers.

## Portal Intake Spec

The portal should collect these groups.

### 1. Project

Purpose:
Define the site instance and template selection.

Fields:

- `site_key`
- `template_key`
- `industry`
- `project_status`
- `assigned_owner`

### 2. Business

Purpose:
Define the business identity and core CTA.

Fields:

- `brand_name`
- `legal_name`
- `business_type`
- `short_tagline`
- `phone`
- `email`
- `primary_cta_label`
- `primary_cta_target`
- `secondary_cta_label`
- `secondary_cta_target`

### 3. Location

Purpose:
Define the geographic and service context.

Fields:

- `city`
- `state_region`
- `country`
- `service_area[]`
- `delivery_modes[]`
- `address_public`

### 4. Audience

Purpose:
Define who the business serves.

Fields:

- `primary_audiences[]`
- `skill_levels[]`
- `special_focus[]`
- `common_pain_points[]`

### 5. Offers

Purpose:
Define the sellable things that become service pages or offer pages.

Each offer should include:

- `record_key`
- `title`
- `slug`
- `category`
- `summary`
- `delivery_mode`
- `duration`
- `price_from`
- `price_notes`
- `audience[]`
- `outcomes[]`
- `cta_label`
- `cta_target`

### 6. Pricing

Purpose:
Define shared pricing behavior.

Fields:

- `currency`
- `pricing_notes`
- `packages_enabled`
- `trial_offer_enabled`
- `trial_offer_text`

### 7. Trust / Proof

Purpose:
Define credibility and differentiation.

Fields:

- `years_experience`
- `credentials[]`
- `highlights[]`
- `testimonials[]`
- `awards[]`

Each testimonial can include:

- `name`
- `quote`
- `role`
- `location`

### 8. Booking / Lead Flow

Purpose:
Define how the site should convert.

Fields:

- `booking_mode`
- `calendar_enabled`
- `booking_notice`
- `lead_type`
- `contact_method_preference`

### 9. FAQ

Purpose:
Capture reusable question-and-answer seed data.

Each FAQ should include:

- `question`
- `answer_seed`
- `page_scope`

### 10. SEO

Purpose:
Provide directional SEO data, not final over-optimized copy.

Fields:

- `brand_keyword`
- `primary_terms[]`
- `secondary_terms[]`
- `location_terms[]`
- `tone`
- `competitor_notes`

### 11. Media

Purpose:
Define reusable assets by stable keys.

Fields:

- `logo_asset_key`
- `primary_photo_asset_key`
- `default_social_image_asset_key`
- `gallery_asset_keys[]`

## Canonical Seed Schema

This is the recommended canonical JSON shape for the portal.

```json
{
  "project": {
    "site_key": "melody-lessons-portland",
    "template_key": "service-business-v1",
    "industry": "music-teacher",
    "project_status": "intake"
  },
  "business": {
    "brand_name": "Melody Lessons Studio",
    "legal_name": "Melody Lessons LLC",
    "business_type": "Private music teacher",
    "short_tagline": "Piano and voice lessons for kids and adults",
    "phone": "503-555-0199",
    "email": "hello@melodylessons.com",
    "primary_cta_label": "Book a Trial Lesson",
    "primary_cta_target": "/book-lesson/",
    "secondary_cta_label": "Contact",
    "secondary_cta_target": "/contact/"
  },
  "location": {
    "city": "Portland",
    "state_region": "Oregon",
    "country": "US",
    "service_area": ["Portland", "Beaverton", "Lake Oswego"],
    "delivery_modes": ["in-person", "online"]
  },
  "audience": {
    "primary_audiences": ["children", "teens", "adults"],
    "skill_levels": ["beginner", "intermediate"],
    "special_focus": ["first-time students", "busy families"]
  },
  "offers": [
    {
      "record_key": "piano-lessons",
      "title": "Private Piano Lessons",
      "slug": "private-piano-lessons",
      "category": "lessons",
      "summary": "One-on-one piano instruction for beginners and intermediate students.",
      "delivery_mode": "in-person or online",
      "duration": "45 minutes",
      "price_from": "$45",
      "price_notes": "Trial lesson available",
      "audience": ["children", "adults"],
      "outcomes": ["reading music", "basic technique", "song confidence"],
      "cta_label": "Book Piano Lessons",
      "cta_target": "/book-lesson/"
    }
  ],
  "pricing": {
    "currency": "USD",
    "pricing_notes": "Monthly packages available on request."
  },
  "proof": {
    "years_experience": "12",
    "credentials": [
      "Bachelor of Music",
      "12 years teaching experience"
    ],
    "highlights": [
      "Patient beginner instruction",
      "Flexible online lessons",
      "Recital preparation support"
    ],
    "testimonials": [
      {
        "name": "Sarah M.",
        "quote": "My daughter looks forward to lessons every week.",
        "role": "Parent"
      }
    ]
  },
  "booking": {
    "booking_mode": "consultation-first",
    "calendar_enabled": true,
    "booking_notice": "New students start with a short trial lesson and placement chat."
  },
  "faq": [
    {
      "question": "Do you teach complete beginners?",
      "answer_seed": "Yes, beginner students are welcome.",
      "page_scope": "home"
    }
  ],
  "seo": {
    "brand_keyword": "Melody Lessons Studio",
    "primary_terms": [
      "piano lessons Portland",
      "voice lessons Portland",
      "music teacher Portland"
    ],
    "secondary_terms": [
      "beginner piano teacher",
      "online voice lessons"
    ],
    "location_terms": ["Portland", "Beaverton"],
    "tone": "clear and professional"
  },
  "media": {
    "logo_asset_key": "melody-logo",
    "primary_photo_asset_key": "teacher-portrait",
    "default_social_image_asset_key": "melody-social"
  }
}
```

## Derived Content Rules

The portal should not store fully written pages as the source of truth.

Instead, the site generator should derive first-pass content from seeds.

Examples:

- Homepage H1:
  - `{Primary Service Category} in {City}`
  - or `{Business Type} for {Primary Audience}`
- Homepage intro:
  - build from `business.short_tagline`, `location`, `audience`, and top `offers`
- Service page title:
  - `{Offer Title} | {Brand Name}`
- Service meta title:
  - `{Offer Title} in {City} | {Brand Name}`
- Service meta description:
  - `{Brand Name} offers {offer title} in {city} for {audience}. {primary CTA or benefit}.`
- FAQ answers:
  - start from `answer_seed`
  - expand with location, delivery mode, or audience where relevant

The system should generate drafts, not final polished brand writing.

## Current WordPress Mapping

This section maps the recommended seed model to the current Lithia theme and WordPress data model.

### Existing Theme Areas

Current code already contains:

- an admin Launch Wizard for local site setup in `inc/launch-wizard.php`
- a seed sync foundation for one-row site scopes in `inc/seed-sync.php`
- a Services import/upsert system in `inc/services.php`

### One-Row Site Scopes

Current seed sync supports:

- `site_settings`
- `business_details`
- `brand_content`
- `site_styles`

These are updated via:

- `blogname`, `blogdescription`, `admin_email`, `timezone_string`
- option `business-details`
- option `brand-content`
- option `lithia_site_styles`

### Current Page Seed Mapping

Page-level records are imported from `pages[]` and stored in both post/meta and normalized option storage.

| Canonical field | Current storage |
| --- | --- |
| `pages[].record_key` | post meta `_lithia_record_key` |
| `pages[].page_role` | post meta `_lithia_page_role` |
| `pages[].headline_seed` | post meta `_lithia_page_seed_headline` |
| `pages[].summary_seed` | post meta `_lithia_page_seed_summary` |
| `pages[].cta_label` | post meta `_lithia_page_seed_cta_label` |
| `pages[].cta_target` | post meta `_lithia_page_seed_cta_target` |
| `pages[].seo_title_seed` | post meta `_lithia_page_seed_seo_title` |
| `pages[].seo_description_seed` | post meta `_lithia_page_seed_seo_description` |
| all normalized page seed rows | option `lithia_project_page_seeds` |

### Current Offer / Service Mapping

In the current theme, `offers` map most closely to the `services` post type.

Recommended mapping:

| Canonical field | Current storage |
| --- | --- |
| `offers[].record_key` | post meta `_lithia_record_key` |
| `offers[].title` | `post_title` |
| `offers[].slug` | `post_name` |
| `offers[].summary` | `post_excerpt`, `service_hero_text`, `service_overview_text` |
| `offers[].delivery_mode` | `service_delivery_mode` |
| `offers[].duration` | `service_timeline` for now, later split into dedicated duration field |
| `offers[].category` | not yet modeled cleanly |
| `offers[].cta_label` | `service_primary_cta_label` |
| `offers[].cta_target` | `service_primary_cta_url` |
| `offers[].price_from` | `service_price_from`, `_app_price`, and mirrored `offer_price_from` |
| `offers[].audience[]` | `service_audience` |
| `offers[].outcomes[]` | `service_outcomes` (and compatibility mapping into `service_highlights` when highlights are not explicitly supplied) |

Canonical alias support:

- importer accepts `offer_*` payload aliases (for CTA, overview, process, pricing, audience, outcomes, provider links)
- importer preserves backward compatibility by still writing `service_*` meta
- importer now also mirrors canonical `offer_*` meta for migration-safe reads

### Current Service Meta Already Supported

The existing service importer and theme already support:

- `service_hero_eyebrow`
- `service_hero_title`
- `service_hero_text`
- `service_primary_cta_label`
- `service_primary_cta_url`
- `service_secondary_cta_label`
- `service_secondary_cta_url`
- `service_overview_heading`
- `service_overview_text`
- `service_highlights_heading`
- `service_highlights`
- `service_process_heading`
- `service_process_steps`
- `service_booking_note`
- `rank_math_title`
- `rank_math_description`
- `rank_math_focus_keyword`
- `rank_math_facebook_title`
- `rank_math_facebook_description`
- `rank_math_twitter_title`
- `rank_math_twitter_description`
- `rank_math_robots`

### Existing Gaps

Current remaining non-P0 gap:

- none currently tracked (as of 2026-04-13 after removing `offer_*` alias reads/writes from importer and project admin workflows)

## Recommended WordPress Data Model Changes

To scale this system, add a shared importer layer and normalize the schema.

### 1. Create a Canonical Importer

Replace the split logic with one importer service that can be called by:

- the local Launch Wizard
- a future portal webhook or API client
- WP-CLI
- optional manual JSON import

The importer should own:

- site settings sync
- options sync
- page upserts
- offer/service upserts
- provider upserts
- FAQ upserts
- asset matching by stable asset key
- SEO meta upserts

### 2. Add Page Seeds

Add a `pages` collection to the canonical schema:

- `home`
- `about`
- `contact`
- `booking`
- optional `platform`

Each page seed should support:

- `record_key`
- `page_role`
- `headline_seed`
- `summary_seed`
- `cta_label`
- `cta_target`
- `seo_title_seed`
- `seo_description_seed`

### 3. Generalize Terminology

For product architecture:

- use `offers` instead of `services` at the schema level
- use `people` or `providers` depending on template
- use `lead_flow` or `booking` instead of a service-only framing

The WordPress storage can still use the current `services` CPT during transition.

## Recommended Generation Flow

The import flow should work like this:

1. Validate the JSON payload.
2. Normalize all `record_key` values.
3. Resolve asset keys.
4. Sync site settings and brand options.
5. Upsert core pages by page role.
6. Upsert offers into the `services` CPT.
7. Upsert providers if relevant.
8. Generate first-pass page content from seeds.
9. Generate baseline SEO values.
10. Save a provisioning report.

## What Should Be Generated vs Entered Manually

### Entered Manually in the Portal

- facts
- structured business details
- offers
- rates
- FAQs
- testimonials
- credentials
- CTA preferences
- media references
- SEO targets

### Generated Automatically

- draft page copy
- service page structure
- first-pass headings
- meta titles
- meta descriptions
- social titles and descriptions
- homepage service cards
- FAQ layout blocks

### Reviewed by a Human Before Launch

- homepage positioning
- final CTA hierarchy
- tone and polish
- internal linking
- image selection
- local SEO nuance
- accessibility and content quality

## Phased Scalability Plan

### Phase 1

Use the current local Launch Wizard plus manual content refinement.

Good for:

- proving the structure
- validating field coverage
- identifying missing schema pieces

### Phase 2

Create a canonical JSON importer and let the local site consume payloads directly.

Good for:

- template repeatability
- automation without a full portal build

### Phase 3

Build the client portal wizard and have it export or push JSON payloads into target sites.

Good for:

- scaling onboarding
- separating intake from production sites

### Phase 4

Add orchestration:

- site provisioning
- deployment state tracking
- QA checklists
- change logs
- re-sync support for approved fields

Phase 4 baseline tracker command:

- `wp-content/themes/lithia-web-service-theme/tests/run-deployment-state-check.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-qa-checklist-audit.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-change-log-audit.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-resync-readiness-check.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-phase4-orchestration-suite.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-release-readiness-suite.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-evidence-manifest-audit.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-release-handoff-record.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-final-certification-suite.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-v1-completion-certificate.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-release-packet-export.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-release-packet-verify.sh`
- `wp-content/themes/lithia-web-service-theme/tests/run-release-closeout-report.sh`

## Current System Truth

As of now:

- the local Launch Wizard can upsert services and providers and apply site-level settings
- the seed sync layer only handles one-row scopes and JSON payload files
- the services import layer already supports richer service and SEO meta than the launch wizard currently uses
- homepage rendering is anchored by the block-theme `templates/front-page.html` layout, not by raw Home page body content alone

That means the next important engineering step is not more homepage marketing.
It is consolidating the import logic into one canonical provisioning layer.

## Recommended Next Build Step

Implement a shared importer with this shape:

- `lithia_import_project_payload( array $payload, array $args = [] ): array`

That importer should become the single entry point for:

- the local wizard
- WP-CLI imports
- future portal-based project provisioning

## Review States

Use these explicit review states:

1. `intake`
2. `approved`
3. `imported`
4. `qa`
5. `launched`

Recommended meaning:

- `intake`: client data is still being collected
- `approved`: internal review says the payload is ready to import
- `imported`: the payload has been applied to the target site
- `qa`: the draft site is under internal review and refinement
- `launched`: the site is live and the project has exited the build pipeline

## Notes For Future Codex Sessions

When extending this system:

- treat portal intake fields as UX, not source-of-truth storage keys
- keep the canonical payload JSON-first
- use stable `record_key` and `asset_key` values everywhere
- derive draft copy from seed facts
- do not let CSV become the primary data model
- prefer generic schema naming even if WordPress storage still uses service-specific meta during transition

## Release Gate

Before claiming "V1 complete", run the Definition Of Done gate and record evidence:

- `Docs/template-system/V1-DEFINITION-OF-DONE.md`
- `Docs/template-system/evidence/README.md`
- `Docs/template-system/evidence/item-3e-release-candidate-handoff.md`
- `Docs/template-system/evidence/item-3f-launch-state-transition.md`
- `Docs/template-system/evidence/item-4a-deployment-state-summary.md`
- `Docs/template-system/evidence/item-4b-qa-checklist-summary.md`
- `Docs/template-system/evidence/item-4c-change-log-summary.md`
- `Docs/template-system/evidence/item-4d-resync-readiness-summary.md`
- `Docs/template-system/evidence/item-4e-phase4-orchestration-suite-summary.md`
- `Docs/template-system/evidence/item-4f-release-readiness-suite-summary.md`
- `Docs/template-system/evidence/item-4g-evidence-manifest-summary.md`
- `Docs/template-system/evidence/item-4h-release-handoff-summary.md`
- `Docs/template-system/evidence/item-4i-final-certification-summary.md`
- `Docs/template-system/evidence/item-4j-v1-completion-summary.md`
- `Docs/template-system/evidence/item-4k-release-packet-summary.md`
- `Docs/template-system/evidence/item-4l-release-packet-verify-summary.md`
- `Docs/template-system/evidence/item-4m-release-timeline-summary.md`
- `Docs/template-system/evidence/item-4n-release-closeout-report.md`
- `Docs/template-system/evidence/item-4o-full-release-sequence-summary.md`
- `Docs/template-system/evidence/item-4p-evidence-integrity-snapshot-summary.md`
- `Docs/template-system/evidence/item-4q-doc-consistency-audit-summary.md`
- `Docs/template-system/evidence/item-4r-release-status-board-summary.md`
- `Docs/template-system/evidence/item-4s-release-artifact-index-summary.md`
- `Docs/template-system/evidence/item-4t-release-attestation-summary.md`
- importer regression runner:
  - `wp-content/themes/lithia-web-service-theme/tests/run-importer-regression.php`
  - `wp-content/themes/lithia-web-service-theme/tests/run-importer-schema-regression.php`
  - `wp-content/themes/lithia-web-service-theme/tests/run-importer-regression-suite.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-v1-gate-check.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-deployment-state-check.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-qa-checklist-audit.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-change-log-audit.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-resync-readiness-check.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-phase4-orchestration-suite.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-readiness-suite.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-evidence-manifest-audit.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-handoff-record.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-final-certification-suite.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-v1-completion-certificate.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-packet-export.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-packet-verify.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-timeline-audit.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-closeout-report.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-full-release-sequence.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-evidence-integrity-snapshot.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-doc-consistency-audit.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-status-board.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-artifact-index.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release-attestation.sh`
  - `wp-content/themes/lithia-web-service-theme/tests/run-release.sh`

Canonical execution order:

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

Optional one-command wrapper (runs steps 1 through 21 and writes aggregate reports):

- `bash wp-content/themes/lithia-web-service-theme/tests/run-release.sh /Users/billybates/Local_Sites/service-site/app/public`
