#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
OUT_MD="$EVID/item-4s-release-artifact-index-summary.md"

artifacts=(
  "3d|item-3d-v1-gate-check.json|.v1_ready // false"
  "4a|item-4a-deployment-state-check.json|.pass // false"
  "4b|item-4b-qa-checklist-audit.json|.pass // false"
  "4c|item-4c-change-log-audit.json|.pass // false"
  "4d|item-4d-resync-readiness-check.json|.pass // false"
  "4e|item-4e-phase4-orchestration-suite.json|.pass // false"
  "4f|item-4f-release-readiness-suite.json|.pass // false"
  "4g|item-4g-evidence-manifest-audit.json|.pass // false"
  "4h|item-4h-release-handoff-record.json|.pass // false"
  "4i|item-4i-final-certification-suite.json|.pass // false"
  "4j|item-4j-v1-completion-certificate.json|.v1_complete // false"
  "4k|item-4k-release-packet-export.json|.pass // false"
  "4l|item-4l-release-packet-verify.json|.pass // false"
  "4m|item-4m-release-timeline-audit.json|.pass // false"
  "4n|item-4n-release-closeout-report.json|.pass // false"
  "4o|item-4o-full-release-sequence.json|.pass // false"
  "4p|item-4p-evidence-integrity-snapshot.json|.pass // false"
  "4q|item-4q-doc-consistency-audit.json|.pass // false"
  "4r|item-4r-release-status-board.json|.pass // false"
)

entries_tmp="$(mktemp)"
missing=0
failing=0

for spec in "${artifacts[@]}"; do
  IFS='|' read -r item rel expr <<< "$spec"
  abs="$EVID/$rel"

  if [[ -f "$abs" ]]; then
    generated_at="$(jq -r '.generated_at // .issued_at // ""' "$abs" 2>/dev/null || echo "")"
    pass="$(jq -r "$expr" "$abs" 2>/dev/null || echo false)"
    if [[ "$pass" != "true" ]]; then
      failing=$((failing + 1))
    fi

    jq -n \
      --arg item "$item" \
      --arg file "$rel" \
      --arg generated_at "$generated_at" \
      --argjson present true \
      --argjson pass "$pass" \
      '{item:$item,file:$file,present:$present,generated_at:$generated_at,pass:$pass}' >> "$entries_tmp"
  else
    missing=$((missing + 1))
    failing=$((failing + 1))
    jq -n \
      --arg item "$item" \
      --arg file "$rel" \
      --argjson present false \
      '{item:$item,file:$file,present:$present,generated_at:"",pass:false}' >> "$entries_tmp"
  fi
done

entries_json="$(jq -s '.' "$entries_tmp")"
present_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==true)] | length')"
pass_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.pass==true)] | length')"

candidate_ok=false
if rg -q 'Candidate: `item-4' "$EVID/README.md"; then
  candidate_ok=true
fi

overall=false
if [[ "$missing" -eq 0 && "$failing" -eq 0 && "$candidate_ok" == "true" ]]; then
  overall=true
fi

report_json="$(jq -n \
  --arg runner "lithia-release-artifact-index-4s" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --argjson entries "$entries_json" \
  --argjson missing "$missing" \
  --argjson failing "$failing" \
  --argjson present "$present_count" \
  --argjson passed "$pass_count" \
  --argjson candidate_ok "$candidate_ok" \
  --argjson overall "$overall" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    totals: {
      artifacts: ($entries | length),
      present: $present,
      missing: $missing,
      passed: $passed,
      failing: $failing
    },
    checks: {
      evidence_index_candidate_item4: $candidate_ok
    },
    entries: $entries,
    pass: $overall
  }')"

status_label="INCOMPLETE"
if [[ "$(printf '%s' "$report_json" | jq -r '.pass')" == "true" ]]; then
  status_label="COMPLETE"
fi

{
  echo "# Item 4.s Release Artifact Index Summary"
  echo
  echo "Date: $(date -u +%Y-%m-%d)"
  echo "Generated at: $(printf '%s' "$report_json" | jq -r '.generated_at')"
  echo
  echo "## Final Status"
  echo
  echo "- Artifact index: **$status_label**"
  echo "- Artifacts present: $(printf '%s' "$report_json" | jq -r '.totals.present')/$(printf '%s' "$report_json" | jq -r '.totals.artifacts')"
  echo "- Artifacts passing: $(printf '%s' "$report_json" | jq -r '.totals.passed')/$(printf '%s' "$report_json" | jq -r '.totals.artifacts')"
  echo
  echo "## Artifact"
  echo
  echo "- item-4s-release-artifact-index.json"
} > "$OUT_MD"

printf '%s\n' "$report_json"
rm -f "$entries_tmp"
