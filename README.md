# Service Site Project Repo

This repository is scoped to custom project code and docs for the local WordPress site at:

- `/Users/billybates/Local_Sites/service-site/app/public`

## What This Repo Tracks

- Custom root scripts:
  - `ChangeSiteDomainService.js`
  - `SearchReplacerWPConfigDomain.js`
- Template-system docs and canonical payload references:
  - `Docs/template-system/...`
- Custom theme source:
  - `wp-content/themes/lithia-web-service-theme/...`

## What This Repo Intentionally Ignores

- WordPress core/runtime files (`wp-admin`, `wp-includes`, root `wp-*.php`)
- Plugins, uploads, backups, packaged zip artifacts
- Generated release/evidence artifacts (JSON and packet dumps)

## Daily Workflow

1. Check status:
```bash
git status --short
```

2. Run regression checks before commit:
```bash
wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-importer-regression.php --path=/Users/billybates/Local_Sites/service-site/app/public
wp eval-file wp-content/themes/lithia-web-service-theme/tests/run-importer-schema-regression.php --path=/Users/billybates/Local_Sites/service-site/app/public
```

3. Run release pipeline when preparing handoff:
```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-release.sh /Users/billybates/Local_Sites/service-site/app/public
```

4. Commit with focused messages:
```bash
git add <targeted-files>
git commit -m "Describe the change clearly"
```

5. Push to GitHub:
```bash
git push origin main
```

## Git Sync Quick Commands

```bash
git fetch origin
git status --short --branch
git log --oneline --decorate -n 8
```

## Site Docs Workflow

- Site docs publish surface: `/site-docs/`
- Runbook: `Docs/template-system/SITE-DOCS-RUNBOOK.md`
- Content model: `wp-content/themes/lithia-web-service-theme/inc/site-docs.php`

## Migration Utilities

- Service meta backfill (dry-run):
```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-service-meta-backfill.sh /Users/billybates/Local_Sites/service-site/app/public dry-run
```

- Service meta backfill (apply):
```bash
bash wp-content/themes/lithia-web-service-theme/tests/run-service-meta-backfill.sh /Users/billybates/Local_Sites/service-site/app/public apply
```
