#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
REL_SUITE="$EVID/item-4f-release-readiness-suite.json"
MANIFEST="$EVID/item-4g-evidence-manifest-audit.json"

extract_json() {
  awk 'BEGIN{emit=0} /^[[:space:]]*\{/ {emit=1} emit{print}'
}

release_pass=false
manifest_pass=false

if [[ -f "$REL_SUITE" ]]; then
  release_pass="$(jq -r '.pass // false' "$REL_SUITE" 2>/dev/null || echo false)"
fi

if [[ -f "$MANIFEST" ]]; then
  manifest_pass="$(jq -r '.pass // false' "$MANIFEST" 2>/dev/null || echo false)"
fi

context_raw="$(wp eval 'echo wp_json_encode(get_option("lithia_project_context", array()));' --path="$ROOT" 2>&1 || true)"
context_json="$(printf '%s\n' "$context_raw" | extract_json)"
if [[ -z "$context_json" ]]; then
  context_json='{}'
fi

review_state="$(printf '%s' "$context_json" | jq -r '.review_state // ""')"
source_review_state="$(printf '%s' "$context_json" | jq -r '.source_review_state // ""')"

jq -n \
  --arg runner "lithia-release-handoff-record-4h" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --arg release_suite_file "$REL_SUITE" \
  --arg manifest_file "$MANIFEST" \
  --arg release_pass "$release_pass" \
  --arg manifest_pass "$manifest_pass" \
  --argjson context "$context_json" \
  --arg review_state "$review_state" \
  --arg source_review_state "$source_review_state" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    inputs: {
      release_suite_file: $release_suite_file,
      evidence_manifest_file: $manifest_file
    },
    checks: {
      release_suite_pass: ($release_pass == "true"),
      evidence_manifest_pass: ($manifest_pass == "true"),
      live_review_state_launched: ($review_state == "launched"),
      live_source_review_state_approved_or_launched: (($source_review_state == "approved") or ($source_review_state == "launched"))
    },
    context: $context,
    pass: (
      (($release_pass == "true"))
      and (($manifest_pass == "true"))
      and (($review_state == "launched"))
      and ((($source_review_state == "approved") or ($source_review_state == "launched")))
    )
  }'
