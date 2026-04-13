#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
OUT_MD="$EVID/item-4t-release-attestation-summary.md"

file_4o="$EVID/item-4o-full-release-sequence.json"
file_4p="$EVID/item-4p-evidence-integrity-snapshot.json"
file_4q="$EVID/item-4q-doc-consistency-audit.json"
file_4r="$EVID/item-4r-release-status-board.json"
file_4s="$EVID/item-4s-release-artifact-index.json"

read_pass() {
  local file="$1"
  if [[ -f "$file" ]]; then
    jq -r '.pass // false' "$file" 2>/dev/null || echo false
  else
    echo false
  fi
}

pass_4o="$(read_pass "$file_4o")"
pass_4p="$(read_pass "$file_4p")"
pass_4q="$(read_pass "$file_4q")"
pass_4r="$(read_pass "$file_4r")"
pass_4s="$(read_pass "$file_4s")"

candidate_ok=false
if rg -q 'Candidate: `item-4t`' "$EVID/README.md"; then
  candidate_ok=true
fi

overall=false
if [[ "$pass_4o" == "true" && "$pass_4p" == "true" && "$pass_4q" == "true" && "$pass_4r" == "true" && "$pass_4s" == "true" && "$candidate_ok" == "true" ]]; then
  overall=true
fi

report_json="$(jq -n \
  --arg runner "lithia-release-attestation-4t" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --arg file_4o "$file_4o" \
  --arg file_4p "$file_4p" \
  --arg file_4q "$file_4q" \
  --arg file_4r "$file_4r" \
  --arg file_4s "$file_4s" \
  --argjson pass_4o "$pass_4o" \
  --argjson pass_4p "$pass_4p" \
  --argjson pass_4q "$pass_4q" \
  --argjson pass_4r "$pass_4r" \
  --argjson pass_4s "$pass_4s" \
  --argjson candidate_ok "$candidate_ok" \
  --argjson overall "$overall" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    inputs: {
      full_release_sequence: $file_4o,
      evidence_integrity_snapshot: $file_4p,
      doc_consistency_audit: $file_4q,
      release_status_board: $file_4r,
      release_artifact_index: $file_4s
    },
    checks: {
      pass_4o: $pass_4o,
      pass_4p: $pass_4p,
      pass_4q: $pass_4q,
      pass_4r: $pass_4r,
      pass_4s: $pass_4s,
      evidence_index_candidate_4t: $candidate_ok
    },
    pass: $overall
  }')"

status_label="INCOMPLETE"
if [[ "$(printf '%s' "$report_json" | jq -r '.pass')" == "true" ]]; then
  status_label="COMPLETE"
fi

{
  echo "# Item 4.t Release Attestation Summary"
  echo
  echo "Date: $(date -u +%Y-%m-%d)"
  echo "Generated at: $(printf '%s' "$report_json" | jq -r '.generated_at')"
  echo
  echo "## Final Status"
  echo
  echo "- Attestation status: **$status_label**"
  echo "- 4.o pass: $(printf '%s' "$report_json" | jq -r '.checks.pass_4o')"
  echo "- 4.p pass: $(printf '%s' "$report_json" | jq -r '.checks.pass_4p')"
  echo "- 4.q pass: $(printf '%s' "$report_json" | jq -r '.checks.pass_4q')"
  echo "- 4.r pass: $(printf '%s' "$report_json" | jq -r '.checks.pass_4r')"
  echo "- 4.s pass: $(printf '%s' "$report_json" | jq -r '.checks.pass_4s')"
  echo
  echo "## Artifact"
  echo
  echo "- item-4t-release-attestation.json"
} > "$OUT_MD"

printf '%s\n' "$report_json"
