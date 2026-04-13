#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
OUT_MD="$EVID/item-4r-release-status-board-summary.md"

file_4o="$EVID/item-4o-full-release-sequence.json"
file_4p="$EVID/item-4p-evidence-integrity-snapshot.json"
file_4q="$EVID/item-4q-doc-consistency-audit.json"

read_bool() {
  local file="$1"
  local expr="$2"
  if [[ -f "$file" ]]; then
    jq -r "$expr" "$file" 2>/dev/null || echo false
  else
    echo false
  fi
}

pass_4o="$(read_bool "$file_4o" '.pass // false')"
pass_4p="$(read_bool "$file_4p" '.pass // false')"
pass_4q="$(read_bool "$file_4q" '.pass // false')"

steps_4o="$(jq -r '.totals.steps // 0' "$file_4o" 2>/dev/null || echo 0)"
failed_4o="$(jq -r '.totals.failed // 0' "$file_4o" 2>/dev/null || echo 0)"
files_4p="$(jq -r '.totals.files // 0' "$file_4p" 2>/dev/null || echo 0)"
hash_failures_4p="$(jq -r '.totals.hash_failures // 0' "$file_4p" 2>/dev/null || echo 0)"
checks_4q="$(jq -r '.totals.checks // 0' "$file_4q" 2>/dev/null || echo 0)"
failed_4q="$(jq -r '.totals.failed // 0' "$file_4q" 2>/dev/null || echo 0)"

candidate_ok=false
if rg -q 'Candidate: `item-4' "$EVID/README.md"; then
  candidate_ok=true
fi

overall=false
if [[ "$pass_4o" == "true" && "$pass_4p" == "true" && "$pass_4q" == "true" && "$candidate_ok" == "true" ]]; then
  overall=true
fi

report_json="$(jq -n \
  --arg runner "lithia-release-status-board-4r" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg file_4o "$file_4o" \
  --arg file_4p "$file_4p" \
  --arg file_4q "$file_4q" \
  --argjson pass_4o "$pass_4o" \
  --argjson pass_4p "$pass_4p" \
  --argjson pass_4q "$pass_4q" \
  --argjson candidate_ok "$candidate_ok" \
  --argjson steps_4o "$steps_4o" \
  --argjson failed_4o "$failed_4o" \
  --argjson files_4p "$files_4p" \
  --argjson hash_failures_4p "$hash_failures_4p" \
  --argjson checks_4q "$checks_4q" \
  --argjson failed_4q "$failed_4q" \
  --argjson overall "$overall" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    inputs: {
      full_release_sequence: $file_4o,
      evidence_integrity_snapshot: $file_4p,
      doc_consistency_audit: $file_4q
    },
    metrics: {
      release_steps_total: $steps_4o,
      release_steps_failed: $failed_4o,
      evidence_files_hashed: $files_4p,
      evidence_hash_failures: $hash_failures_4p,
      doc_checks_total: $checks_4q,
      doc_checks_failed: $failed_4q
    },
    checks: {
      full_release_sequence_pass: $pass_4o,
      evidence_integrity_pass: $pass_4p,
      doc_consistency_pass: $pass_4q,
      evidence_index_candidate_item4: $candidate_ok
    },
    pass: $overall
  }')"

status_label="INCOMPLETE"
if [[ "$(printf '%s' "$report_json" | jq -r '.pass')" == "true" ]]; then
  status_label="COMPLETE"
fi

{
  echo "# Item 4.r Release Status Board Summary"
  echo
  echo "Date: $(date -u +%Y-%m-%d)"
  echo "Generated at: $(printf '%s' "$report_json" | jq -r '.generated_at')"
  echo
  echo "## Final Status"
  echo
  echo "- Status board: **$status_label**"
  echo "- Full release sequence pass: $(printf '%s' "$report_json" | jq -r '.checks.full_release_sequence_pass')"
  echo "- Evidence integrity pass: $(printf '%s' "$report_json" | jq -r '.checks.evidence_integrity_pass')"
  echo "- Doc consistency pass: $(printf '%s' "$report_json" | jq -r '.checks.doc_consistency_pass')"
  echo
  echo "## Artifact"
  echo
  echo "- item-4r-release-status-board.json"
} > "$OUT_MD"

printf '%s\n' "$report_json"
