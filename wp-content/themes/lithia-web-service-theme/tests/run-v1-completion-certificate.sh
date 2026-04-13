#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
FINAL_CERT="$EVID/item-4i-final-certification-suite.json"

extract_json() {
  awk 'BEGIN{emit=0} /^[[:space:]]*\{/ {emit=1} emit{print}'
}

final_pass=false
if [[ -f "$FINAL_CERT" ]]; then
  final_pass="$(jq -r '.pass // false' "$FINAL_CERT" 2>/dev/null || echo false)"
fi

context_raw="$(wp eval 'echo wp_json_encode(get_option("lithia_project_context", array()));' --path="$ROOT" 2>&1 || true)"
context_json="$(printf '%s\n' "$context_raw" | extract_json)"
if [[ -z "$context_json" ]]; then
  context_json='{}'
fi

review_state="$(printf '%s' "$context_json" | jq -r '.review_state // ""')"
source_state="$(printf '%s' "$context_json" | jq -r '.source_review_state // ""')"

jq -n \
  --arg runner "lithia-v1-completion-certificate-4j" \
  --arg issued_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --arg final_cert_file "$FINAL_CERT" \
  --arg final_pass "$final_pass" \
  --arg review_state "$review_state" \
  --arg source_state "$source_state" \
  --argjson context "$context_json" \
  '{
    runner: $runner,
    issued_at: $issued_at,
    root: $root,
    source: {
      final_certification_file: $final_cert_file,
      final_certification_pass: ($final_pass == "true")
    },
    context: $context,
    checks: {
      final_certification_pass: ($final_pass == "true"),
      review_state_launched: ($review_state == "launched"),
      source_review_state_approved_or_launched: (($source_state == "approved") or ($source_state == "launched"))
    },
    v1_complete: (
      (($final_pass == "true"))
      and (($review_state == "launched"))
      and ((($source_state == "approved") or ($source_state == "launched")))
    )
  }'
