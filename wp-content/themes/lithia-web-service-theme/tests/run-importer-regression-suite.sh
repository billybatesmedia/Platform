#!/usr/bin/env bash
set -euo pipefail

ROOT_PATH="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
THEME_TESTS="wp-content/themes/lithia-web-service-theme/tests"

TMP_A="$(mktemp)"
TMP_B="$(mktemp)"
trap 'rm -f "$TMP_A" "$TMP_B"' EXIT

wp eval-file "$THEME_TESTS/run-importer-regression.php" --path="$ROOT_PATH" 2>&1 \
  | sed -n '/^{/,$p' > "$TMP_A"

wp eval-file "$THEME_TESTS/run-importer-schema-regression.php" --path="$ROOT_PATH" 2>&1 \
  | sed -n '/^{/,$p' > "$TMP_B"

A_FAILED="$(jq -r '.totals.failed // 999' "$TMP_A")"
B_FAILED="$(jq -r '.totals.failed // 999' "$TMP_B")"

jq -n \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg runner "lithia-importer-regression-suite-3c" \
  --argjson a "$(cat "$TMP_A")" \
  --argjson b "$(cat "$TMP_B")" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    totals: {
      tests: (($a.totals.tests // 0) + ($b.totals.tests // 0)),
      passed: (($a.totals.passed // 0) + ($b.totals.passed // 0)),
      failed: (($a.totals.failed // 0) + ($b.totals.failed // 0))
    },
    runners: {
      importer_regression: $a,
      schema_regression: $b
    }
  }'

if [[ "$A_FAILED" != "0" || "$B_FAILED" != "0" ]]; then
  exit 1
fi
