#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
MODE="${2:-dry-run}"
SCRIPT_REL="wp-content/themes/lithia-web-service-theme/tests/run-service-meta-backfill.php"

APPLY_FLAG=0
if [[ "$MODE" == "apply" ]]; then
  APPLY_FLAG=1
fi

LITHIA_BACKFILL_APPLY="$APPLY_FLAG" \
wp eval-file "$SCRIPT_REL" --path="$ROOT"

