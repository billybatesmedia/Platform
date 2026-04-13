#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
TESTS_DIR="$ROOT/wp-content/themes/lithia-web-service-theme/tests"

results_tmp="$(mktemp)"

run_and_capture() {
  local name="$1"
  local cmd="$2"
  local out_file="$3"
  if eval "$cmd" > "$out_file"; then
    jq -n \
      --arg name "$name" \
      --arg file "$out_file" \
      --argjson pass "$(jq -r '.pass // .v1_ready // false' "$out_file" 2>/dev/null || echo false)" \
      '{name:$name,file:$file,pass:$pass}'
  else
    jq -n --arg name "$name" --arg file "$out_file" '{name:$name,file:$file,pass:false,error:"runner command failed"}'
  fi
}

run_and_capture \
  "v1_gate_check_3d" \
  "bash \"$TESTS_DIR/run-v1-gate-check.sh\" \"$ROOT\"" \
  "$EVID/item-3d-v1-gate-check.json" >> "$results_tmp"

run_and_capture \
  "phase4_orchestration_suite_4e" \
  "bash \"$TESTS_DIR/run-phase4-orchestration-suite.sh\" \"$ROOT\"" \
  "$EVID/item-4e-phase4-orchestration-suite.json" >> "$results_tmp"

run_and_capture \
  "release_readiness_suite_4f" \
  "bash \"$TESTS_DIR/run-release-readiness-suite.sh\" \"$ROOT\"" \
  "$EVID/item-4f-release-readiness-suite.json" >> "$results_tmp"

run_and_capture \
  "evidence_manifest_audit_4g" \
  "bash \"$TESTS_DIR/run-evidence-manifest-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4g-evidence-manifest-audit.json" >> "$results_tmp"

run_and_capture \
  "release_handoff_record_4h" \
  "bash \"$TESTS_DIR/run-release-handoff-record.sh\" \"$ROOT\"" \
  "$EVID/item-4h-release-handoff-record.json" >> "$results_tmp"

results_json="$(jq -s '.' "$results_tmp")"
failed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==false)] | length')"
passed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==true)] | length')"

jq -n \
  --arg runner "lithia-final-certification-suite-4i" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --argjson checks "$results_json" \
  --argjson passed "$passed_count" \
  --argjson failed "$failed_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    totals: {
      checks: ($passed + $failed),
      passed: $passed,
      failed: $failed
    },
    checks: $checks,
    pass: ($failed == 0)
  }'

rm -f "$results_tmp"
