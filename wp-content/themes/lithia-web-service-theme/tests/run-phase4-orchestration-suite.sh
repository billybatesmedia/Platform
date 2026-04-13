#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
TESTS_DIR="$ROOT/wp-content/themes/lithia-web-service-theme/tests"

PAYLOAD_FALLBACK="${LITHIA_RESYNC_PAYLOAD_FILE:-$EVID/item-1a-approved-payload.json}"

run_and_capture() {
  local name="$1"
  local cmd="$2"
  local out_file="$3"
  if eval "$cmd" > "$out_file"; then
    jq -n \
      --arg name "$name" \
      --arg file "$out_file" \
      --argjson pass "$(jq -r '.pass // false' "$out_file" 2>/dev/null || echo false)" \
      '{name:$name,file:$file,pass:$pass}'
  else
    jq -n --arg name "$name" --arg file "$out_file" '{name:$name,file:$file,pass:false,error:"runner command failed"}'
  fi
}

results_tmp="$(mktemp)"

run_and_capture \
  "item_4a_deployment_state" \
  "bash \"$TESTS_DIR/run-deployment-state-check.sh\" \"$ROOT\"" \
  "$EVID/item-4a-deployment-state-check.json" >> "$results_tmp"

run_and_capture \
  "item_4b_qa_checklist" \
  "bash \"$TESTS_DIR/run-qa-checklist-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4b-qa-checklist-audit.json" >> "$results_tmp"

run_and_capture \
  "item_4c_change_log" \
  "bash \"$TESTS_DIR/run-change-log-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4c-change-log-audit.json" >> "$results_tmp"

run_and_capture \
  "item_4d_resync_readiness" \
  "LITHIA_RESYNC_PAYLOAD_FILE=\"$PAYLOAD_FALLBACK\" bash \"$TESTS_DIR/run-resync-readiness-check.sh\" \"$ROOT\"" \
  "$EVID/item-4d-resync-readiness-check.json" >> "$results_tmp"

results_json="$(jq -s '.' "$results_tmp")"
failed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==false)] | length')"
passed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==true)] | length')"

jq -n \
  --arg runner "lithia-phase4-orchestration-suite-4e" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --arg payload_fallback "$PAYLOAD_FALLBACK" \
  --argjson checks "$results_json" \
  --argjson passed "$passed_count" \
  --argjson failed "$failed_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    payload_fallback: $payload_fallback,
    totals: {
      checks: ($passed + $failed),
      passed: $passed,
      failed: $failed
    },
    checks: $checks,
    pass: ($failed == 0)
  }'

rm -f "$results_tmp"
