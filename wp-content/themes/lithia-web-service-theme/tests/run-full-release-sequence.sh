#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
TESTS_DIR="$ROOT/wp-content/themes/lithia-web-service-theme/tests"
OUT_MD="$EVID/item-4o-full-release-sequence-summary.md"

PAYLOAD_FALLBACK="${LITHIA_RESYNC_PAYLOAD_FILE:-$EVID/item-1a-approved-payload.json}"

extract_json() {
  awk 'BEGIN{emit=0} /^[[:space:]]*\{/ {emit=1} emit{print}'
}

results_tmp="$(mktemp)"

run_step() {
  local index="$1"
  local id="$2"
  local cmd="$3"
  local out_file="$4"
  local pass_expr="$5"

  local raw_tmp="$(mktemp)"
  local command_ok="true"
  local json_ok="false"
  local pass="false"
  local error=""

  if eval "$cmd" >"$raw_tmp" 2>&1; then
    command_ok="true"
  else
    command_ok="false"
  fi

  if extract_json < "$raw_tmp" > "$out_file"; then
    if [[ -s "$out_file" ]] && jq -e '.' "$out_file" >/dev/null 2>&1; then
      json_ok="true"
    else
      json_ok="false"
      error="invalid_or_missing_json_output"
    fi
  else
    json_ok="false"
    error="failed_to_extract_json_output"
  fi

  if [[ "$json_ok" == "true" ]]; then
    if jq -e "$pass_expr" "$out_file" >/dev/null 2>&1; then
      pass="true"
    else
      pass="false"
      if [[ -z "$error" ]]; then
        error="pass_condition_failed"
      fi
    fi
  fi

  if [[ "$command_ok" != "true" ]] && [[ -z "$error" ]]; then
    error="runner_command_failed"
  fi

  local stderr_tail
  stderr_tail="$(tail -n 8 "$raw_tmp" | tr '\n' ' ' | sed 's/[[:space:]]\+/ /g' | sed 's/^ //; s/ $//')"

  jq -n \
    --argjson index "$index" \
    --arg id "$id" \
    --arg command "$cmd" \
    --arg output "$out_file" \
    --argjson command_ok "$command_ok" \
    --argjson json_ok "$json_ok" \
    --argjson pass "$pass" \
    --arg error "$error" \
    --arg stderr_tail "$stderr_tail" \
    '{
      index: $index,
      id: $id,
      command: $command,
      output_file: $output,
      command_ok: $command_ok,
      json_ok: $json_ok,
      pass: $pass,
      error: (if $error == "" then null else $error end),
      output_tail: (if $stderr_tail == "" then null else $stderr_tail end)
    }' >> "$results_tmp"

  rm -f "$raw_tmp"
}

run_step 1 "3c_regression_suite" \
  "bash \"$TESTS_DIR/run-importer-regression-suite.sh\" \"$ROOT\"" \
  "$EVID/item-3c-regression-suite.json" \
  '.totals.failed == 0'

run_step 2 "3d_v1_gate_check" \
  "bash \"$TESTS_DIR/run-v1-gate-check.sh\" \"$ROOT\"" \
  "$EVID/item-3d-v1-gate-check.json" \
  '.v1_ready == true'

run_step 3 "4a_deployment_state" \
  "bash \"$TESTS_DIR/run-deployment-state-check.sh\" \"$ROOT\"" \
  "$EVID/item-4a-deployment-state-check.json" \
  '.pass == true'

run_step 4 "4b_qa_checklist_audit" \
  "bash \"$TESTS_DIR/run-qa-checklist-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4b-qa-checklist-audit.json" \
  '.pass == true'

run_step 5 "4c_change_log_audit" \
  "bash \"$TESTS_DIR/run-change-log-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4c-change-log-audit.json" \
  '.pass == true'

run_step 6 "4d_resync_readiness" \
  "LITHIA_RESYNC_PAYLOAD_FILE=\"$PAYLOAD_FALLBACK\" bash \"$TESTS_DIR/run-resync-readiness-check.sh\" \"$ROOT\"" \
  "$EVID/item-4d-resync-readiness-check.json" \
  '.pass == true'

run_step 7 "4e_phase4_orchestration" \
  "LITHIA_RESYNC_PAYLOAD_FILE=\"$PAYLOAD_FALLBACK\" bash \"$TESTS_DIR/run-phase4-orchestration-suite.sh\" \"$ROOT\"" \
  "$EVID/item-4e-phase4-orchestration-suite.json" \
  '.pass == true'

run_step 8 "4f_release_readiness" \
  "bash \"$TESTS_DIR/run-release-readiness-suite.sh\" \"$ROOT\"" \
  "$EVID/item-4f-release-readiness-suite.json" \
  '.pass == true'

run_step 9 "4g_evidence_manifest" \
  "bash \"$TESTS_DIR/run-evidence-manifest-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4g-evidence-manifest-audit.json" \
  '.pass == true'

run_step 10 "4h_release_handoff" \
  "bash \"$TESTS_DIR/run-release-handoff-record.sh\" \"$ROOT\"" \
  "$EVID/item-4h-release-handoff-record.json" \
  '.pass == true'

run_step 11 "4i_final_certification" \
  "bash \"$TESTS_DIR/run-final-certification-suite.sh\" \"$ROOT\"" \
  "$EVID/item-4i-final-certification-suite.json" \
  '.pass == true'

run_step 12 "4j_v1_completion" \
  "bash \"$TESTS_DIR/run-v1-completion-certificate.sh\" \"$ROOT\"" \
  "$EVID/item-4j-v1-completion-certificate.json" \
  '.v1_complete == true'

run_step 13 "4k_release_packet_export" \
  "bash \"$TESTS_DIR/run-release-packet-export.sh\" \"$ROOT\"" \
  "$EVID/item-4k-release-packet-export.json" \
  '.pass == true'

run_step 14 "4l_release_packet_verify" \
  "bash \"$TESTS_DIR/run-release-packet-verify.sh\" \"$ROOT\"" \
  "$EVID/item-4l-release-packet-verify.json" \
  '.pass == true'

run_step 15 "4m_release_timeline" \
  "bash \"$TESTS_DIR/run-release-timeline-audit.sh\" \"$ROOT\"" \
  "$EVID/item-4m-release-timeline-audit.json" \
  '.pass == true'

run_step 16 "4n_release_closeout" \
  "bash \"$TESTS_DIR/run-release-closeout-report.sh\" \"$ROOT\"" \
  "$EVID/item-4n-release-closeout-report.json" \
  '.pass == true'

results_json="$(jq -s '.' "$results_tmp")"
passed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==true)] | length')"
failed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==false)] | length')"

report_json="$(jq -n \
  --arg runner "lithia-full-release-sequence-4o" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --arg payload_fallback "$PAYLOAD_FALLBACK" \
  --argjson steps "$results_json" \
  --argjson passed "$passed_count" \
  --argjson failed "$failed_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    payload_fallback: $payload_fallback,
    totals: {
      steps: ($passed + $failed),
      passed: $passed,
      failed: $failed
    },
    steps: $steps,
    pass: ($failed == 0)
  }')"

status_label="INCOMPLETE"
if [[ "$(printf '%s' "$report_json" | jq -r '.pass')" == "true" ]]; then
  status_label="COMPLETE"
fi

{
  echo "# Item 4.o Full Release Sequence Summary"
  echo
  echo "Date: $(date -u +%Y-%m-%d)"
  echo "Generated at: $(printf '%s' "$report_json" | jq -r '.generated_at')"
  echo
  echo "## Final Status"
  echo
  echo "- Release sequence status: **$status_label**"
  echo "- Steps passed: $(printf '%s' "$report_json" | jq -r '.totals.passed')/$(printf '%s' "$report_json" | jq -r '.totals.steps')"
  echo
  echo "## Step Results"
  echo
  while IFS= read -r line; do
    echo "- $line"
  done < <(printf '%s' "$report_json" | jq -r '.steps[] | "\(.index). \(.id): pass=\(.pass) command_ok=\(.command_ok) json_ok=\(.json_ok)"')
  echo
  echo "## Artifact"
  echo
  echo "- item-4o-full-release-sequence.json"
} > "$OUT_MD"

printf '%s\n' "$report_json"
rm -f "$results_tmp"
