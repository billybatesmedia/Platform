#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"

required=(
  "item-1a-import-stability-report.md"
  "item-1b-profile-coverage-report.md"
  "item-1c-ui-cli-parity-report.md"
  "item-1d-critical-error-disposition.md"
  "item-2a-price-from-coverage-report.md"
  "item-2b-price-notes-coverage-report.md"
  "item-2c-audience-coverage-report.md"
  "item-2d-outcomes-coverage-report.md"
  "item-2e-faq-coverage-report.md"
  "item-2f-proof-testimonials-coverage-report.md"
  "item-2g-page-seeds-coverage-report.md"
  "item-2h-generic-offers-coverage-report.md"
  "item-3a-regression-test-summary.md"
  "item-3b-schema-regression-summary.md"
  "item-3c-regression-suite-summary.md"
  "item-3d-v1-gate-check.json"
  "item-3e-release-candidate-handoff.md"
  "item-3f-launch-state-transition.md"
  "item-4a-deployment-state-check.json"
  "item-4b-qa-checklist-audit.json"
  "item-4c-change-log-audit.json"
  "item-4d-resync-readiness-check.json"
  "item-4e-phase4-orchestration-suite.json"
  "item-4f-release-readiness-suite.json"
)

entries_tmp="$(mktemp)"

for rel in "${required[@]}"; do
  abs="$EVID/$rel"
  if [[ -f "$abs" ]]; then
    size="$(wc -c < "$abs" | tr -d ' ')"
    sha="$(openssl dgst -sha256 "$abs" 2>/dev/null | awk '{print $NF}')"
    if [[ -z "$sha" ]]; then
      sha="$(sha256sum "$abs" 2>/dev/null | awk '{print $1}')"
    fi
    if [[ -z "$sha" ]]; then
      sha="unavailable"
    fi
    jq -n \
      --arg file "$rel" \
      --argjson present true \
      --argjson size "$size" \
      --arg sha256 "$sha" \
      '{file:$file,present:$present,size:$size,sha256:$sha256}' >> "$entries_tmp"
  else
    jq -n \
      --arg file "$rel" \
      --argjson present false \
      '{file:$file,present:$present,size:0,sha256:""}' >> "$entries_tmp"
  fi
done

entries_json="$(jq -s '.' "$entries_tmp")"
missing_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==false)] | length')"

jq -n \
  --arg runner "lithia-evidence-manifest-audit-4g" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg evidence_dir "$EVID" \
  --argjson entries "$entries_json" \
  --argjson missing "$missing_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    evidence_dir: $evidence_dir,
    totals: {
      required: ($entries | length),
      missing: $missing
    },
    entries: $entries,
    pass: ($missing == 0)
  }'

rm -f "$entries_tmp"
