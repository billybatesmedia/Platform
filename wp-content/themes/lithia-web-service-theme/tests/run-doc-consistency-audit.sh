#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
DOCS_DIR="$ROOT/Docs/template-system"
TESTS_DIR="$ROOT/wp-content/themes/lithia-web-service-theme/tests"
OUT_MD="$EVID/item-4q-doc-consistency-audit-summary.md"

results_tmp="$(mktemp)"

add_check() {
  local name="$1"
  local pass="$2"
  local detail="$3"
  jq -n --arg name "$name" --argjson pass "$pass" --arg detail "$detail" '{name:$name,pass:$pass,detail:$detail}' >> "$results_tmp"
}

check_file_exists() {
  local label="$1"
  local file="$2"
  if [[ -f "$file" ]]; then
    add_check "$label" true "present"
  else
    add_check "$label" false "missing: $file"
  fi
}

check_pattern_in_file() {
  local label="$1"
  local pattern="$2"
  local file="$3"
  if rg -q "$pattern" "$file"; then
    add_check "$label" true "pattern present"
  else
    add_check "$label" false "pattern missing: $pattern in $file"
  fi
}

# Core docs exist
check_file_exists "docs_readme_present" "$DOCS_DIR/README.md"
check_file_exists "docs_operator_checklist_present" "$DOCS_DIR/OPERATOR-CHECKLIST.md"
check_file_exists "docs_v1_dod_present" "$DOCS_DIR/V1-DEFINITION-OF-DONE.md"
check_file_exists "docs_evidence_index_present" "$EVID/README.md"

# Release scripts exist
required_scripts=(
  "run-importer-regression-suite.sh"
  "run-v1-gate-check.sh"
  "run-deployment-state-check.sh"
  "run-qa-checklist-audit.sh"
  "run-change-log-audit.sh"
  "run-resync-readiness-check.sh"
  "run-phase4-orchestration-suite.sh"
  "run-release-readiness-suite.sh"
  "run-evidence-manifest-audit.sh"
  "run-release-handoff-record.sh"
  "run-final-certification-suite.sh"
  "run-v1-completion-certificate.sh"
  "run-release-packet-export.sh"
  "run-release-packet-verify.sh"
  "run-release-timeline-audit.sh"
  "run-release-closeout-report.sh"
  "run-full-release-sequence.sh"
  "run-evidence-integrity-snapshot.sh"
  "run-release-status-board.sh"
  "run-release-artifact-index.sh"
  "run-release-attestation.sh"
  "run-release.sh"
)

for s in "${required_scripts[@]}"; do
  check_file_exists "script_${s}_present" "$TESTS_DIR/$s"
done

# Evidence summaries exist through 4.s
required_summaries=(
  "item-3e-release-candidate-handoff.md"
  "item-3f-launch-state-transition.md"
  "item-4a-deployment-state-summary.md"
  "item-4b-qa-checklist-summary.md"
  "item-4c-change-log-summary.md"
  "item-4d-resync-readiness-summary.md"
  "item-4e-phase4-orchestration-suite-summary.md"
  "item-4f-release-readiness-suite-summary.md"
  "item-4g-evidence-manifest-summary.md"
  "item-4h-release-handoff-summary.md"
  "item-4i-final-certification-summary.md"
  "item-4j-v1-completion-summary.md"
  "item-4k-release-packet-summary.md"
  "item-4l-release-packet-verify-summary.md"
  "item-4m-release-timeline-summary.md"
  "item-4n-release-closeout-report.md"
  "item-4o-full-release-sequence-summary.md"
  "item-4p-evidence-integrity-snapshot-summary.md"
  "item-4r-release-status-board-summary.md"
)

for rel in "${required_summaries[@]}"; do
  check_file_exists "evidence_${rel}_present" "$EVID/$rel"
done

# New release-chain references
check_pattern_in_file "evidence_index_candidate_item4" 'Candidate: `item-4' "$EVID/README.md"
check_pattern_in_file "readme_mentions_4q" "item-4q-doc-consistency-audit-summary" "$DOCS_DIR/README.md"
check_pattern_in_file "readme_mentions_4r" "item-4r-release-status-board-summary" "$DOCS_DIR/README.md"
check_pattern_in_file "readme_mentions_4s" "item-4s-release-artifact-index-summary" "$DOCS_DIR/README.md"
check_pattern_in_file "readme_mentions_4t" "item-4t-release-attestation-summary" "$DOCS_DIR/README.md"
check_pattern_in_file "operator_mentions_4q" "Doc Consistency Audit" "$DOCS_DIR/OPERATOR-CHECKLIST.md"
check_pattern_in_file "operator_mentions_4r" "Release Status Board" "$DOCS_DIR/OPERATOR-CHECKLIST.md"
check_pattern_in_file "operator_mentions_4s" "Release Artifact Index" "$DOCS_DIR/OPERATOR-CHECKLIST.md"
check_pattern_in_file "operator_mentions_4t" "Release Attestation" "$DOCS_DIR/OPERATOR-CHECKLIST.md"
check_pattern_in_file "dod_mentions_4q" "item-4q-doc-consistency-audit-summary" "$DOCS_DIR/V1-DEFINITION-OF-DONE.md"
check_pattern_in_file "dod_mentions_4r" "item-4r-release-status-board-summary" "$DOCS_DIR/V1-DEFINITION-OF-DONE.md"
check_pattern_in_file "dod_mentions_4s" "item-4s-release-artifact-index-summary" "$DOCS_DIR/V1-DEFINITION-OF-DONE.md"
check_pattern_in_file "dod_mentions_4t" "item-4t-release-attestation-summary" "$DOCS_DIR/V1-DEFINITION-OF-DONE.md"
check_pattern_in_file "alias_runs_doc_audit" "run-doc-consistency-audit.sh" "$TESTS_DIR/run-release.sh"
check_pattern_in_file "alias_runs_status_board" "run-release-status-board.sh" "$TESTS_DIR/run-release.sh"
check_pattern_in_file "alias_runs_artifact_index" "run-release-artifact-index.sh" "$TESTS_DIR/run-release.sh"
check_pattern_in_file "alias_runs_attestation" "run-release-attestation.sh" "$TESTS_DIR/run-release.sh"

results_json="$(jq -s '.' "$results_tmp")"
passed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==true)] | length')"
failed_count="$(printf '%s' "$results_json" | jq '[.[] | select(.pass==false)] | length')"

report_json="$(jq -n \
  --arg runner "lithia-doc-consistency-audit-4q" \
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
  }')"

status_label="INCOMPLETE"
if [[ "$(printf '%s' "$report_json" | jq -r '.pass')" == "true" ]]; then
  status_label="COMPLETE"
fi

{
  echo "# Item 4.q Documentation Consistency Audit Summary"
  echo
  echo "Date: $(date -u +%Y-%m-%d)"
  echo "Generated at: $(printf '%s' "$report_json" | jq -r '.generated_at')"
  echo
  echo "## Final Status"
  echo
  echo "- Doc consistency status: **$status_label**"
  echo "- Checks passed: $(printf '%s' "$report_json" | jq -r '.totals.passed')/$(printf '%s' "$report_json" | jq -r '.totals.checks')"
  echo
  echo "## Artifact"
  echo
  echo "- item-4q-doc-consistency-audit.json"
} > "$OUT_MD"

printf '%s\n' "$report_json"
rm -f "$results_tmp"
