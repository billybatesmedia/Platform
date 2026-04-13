#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"

artifacts=(
  "item-3d-v1-gate-check.json"
  "item-4a-deployment-state-check.json"
  "item-4b-qa-checklist-audit.json"
  "item-4c-change-log-audit.json"
  "item-4d-resync-readiness-check.json"
  "item-4e-phase4-orchestration-suite.json"
  "item-4f-release-readiness-suite.json"
  "item-4g-evidence-manifest-audit.json"
  "item-4h-release-handoff-record.json"
  "item-4i-final-certification-suite.json"
  "item-4j-v1-completion-certificate.json"
  "item-4k-release-packet-export.json"
  "item-4l-release-packet-verify.json"
)

entries_tmp="$(mktemp)"
missing=0

for rel in "${artifacts[@]}"; do
  abs="$EVID/$rel"
  if [[ -f "$abs" ]]; then
    ts="$(jq -r '.generated_at // .issued_at // ""' "$abs" 2>/dev/null || echo "")"
    pass_flag="$(jq -r '.pass // .v1_ready // .v1_complete // false' "$abs" 2>/dev/null || echo false)"
    jq -n \
      --arg file "$rel" \
      --arg ts "$ts" \
      --argjson present true \
      --argjson pass "$pass_flag" \
      '{file:$file,present:$present,timestamp:$ts,pass:$pass}' >> "$entries_tmp"
  else
    missing=$((missing + 1))
    jq -n \
      --arg file "$rel" \
      --argjson present false \
      '{file:$file,present:$present,timestamp:"",pass:false}' >> "$entries_tmp"
  fi
done

entries_json="$(jq -s '.' "$entries_tmp")"
with_ts_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==true and .timestamp!="")] | length')"
pass_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==true and .pass==true)] | length')"

timeline_sorted="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==true)] | sort_by(.timestamp)')"
timeline_total="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==true)] | length')"

jq -n \
  --arg runner "lithia-release-timeline-audit-4m" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --argjson entries "$entries_json" \
  --argjson timeline "$timeline_sorted" \
  --argjson missing "$missing" \
  --argjson with_ts "$with_ts_count" \
  --argjson pass_count "$pass_count" \
  --argjson total "$timeline_total" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    totals: {
      artifacts: ($entries | length),
      present: $total,
      missing: $missing,
      with_timestamp: $with_ts,
      passing: $pass_count
    },
    entries: $entries,
    timeline: $timeline,
    pass: (
      ($missing == 0)
      and ($with_ts == $total)
      and ($pass_count == $total)
    )
  }'

rm -f "$entries_tmp"
