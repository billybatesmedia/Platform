#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"

pass() { echo "{\"name\":\"$1\",\"pass\":true,\"meta\":$2}"; }
fail() { echo "{\"name\":\"$1\",\"pass\":false,\"meta\":$2}"; }

results=()

# Gate 1 evidence
if [[ -f "$EVID/item-1a-cycle-summary.tsv" ]]; then
  results+=("$(pass gate1_import_stability_cycle_summary '{}')")
else
  results+=("$(fail gate1_import_stability_cycle_summary '{"reason":"missing item-1a-cycle-summary.tsv"}')")
fi

if [[ -f "$EVID/item-1b-profile-summary.tsv" ]]; then
  results+=("$(pass gate1_profile_coverage_summary '{}')")
else
  results+=("$(fail gate1_profile_coverage_summary '{"reason":"missing item-1b-profile-summary.tsv"}')")
fi

if [[ -f "$EVID/item-1c-parity-result.json" ]] && jq -e '.dry_equal==true and .apply_equal==true' "$EVID/item-1c-parity-result.json" >/dev/null 2>&1; then
  results+=("$(pass gate1_ui_cli_parity '{"dry_equal":true,"apply_equal":true}')")
else
  results+=("$(fail gate1_ui_cli_parity '{"reason":"missing/failed item-1c-parity-result.json"}')")
fi

if [[ -f "$EVID/item-1d-critical-log-counts.tsv" ]]; then
  results+=("$(pass gate1_critical_error_disposition '{}')")
else
  results+=("$(fail gate1_critical_error_disposition '{"reason":"missing item-1d-critical-log-counts.tsv"}')")
fi

# Gate 2 evidence
for item in 2a 2b 2c 2d 2e 2f 2g 2h; do
  if [[ -f "$EVID/item-$item-apply.json" ]]; then
    results+=("$(pass gate2_item_${item}_apply_present '{}')")
  else
    results+=("$(fail gate2_item_${item}_apply_present '{"reason":"missing apply artifact"}')")
  fi
done

# Gate 3 required artifact
if [[ -f "$EVID/item-3d-qa-signoff-notes.md" ]]; then
  results+=("$(pass gate3_qa_signoff_notes_present '{}')")
else
  results+=("$(fail gate3_qa_signoff_notes_present '{"reason":"missing QA signoff notes"}')")
fi

# Gate 4 required artifact
if [[ -f "$EVID/item-3d-rollback-verification-note.md" ]]; then
  results+=("$(pass gate4_rollback_note_present '{}')")
else
  results+=("$(fail gate4_rollback_note_present '{"reason":"missing rollback verification note"}')")
fi

# Gate 5 test baseline
if [[ -f "$EVID/item-3c-regression-suite.json" ]] && jq -e '.totals.failed==0' "$EVID/item-3c-regression-suite.json" >/dev/null 2>&1; then
  results+=("$(pass gate5_regression_suite_pass '{"failed":0}')")
else
  results+=("$(fail gate5_regression_suite_pass '{"reason":"missing or failing suite output"}')")
fi

json_array=$(printf '%s\n' "${results[@]}" | jq -s '.')
failed_count=$(printf '%s' "$json_array" | jq '[.[] | select(.pass==false)] | length')
pass_count=$(printf '%s' "$json_array" | jq '[.[] | select(.pass==true)] | length')

jq -n \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg runner "lithia-v1-gate-check-3d" \
  --argjson checks "$json_array" \
  --argjson passed "$pass_count" \
  --argjson failed "$failed_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    totals: {
      checks: ($passed + $failed),
      passed: $passed,
      failed: $failed
    },
    checks: $checks,
    v1_ready: ($failed == 0)
  }'
