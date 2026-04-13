#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"

extract_json() {
  awk 'BEGIN{emit=0} /^[[:space:]]*\{/ {emit=1} emit{print}'
}

mtime_iso() {
  local file="$1"
  local epoch
  epoch="$(stat -f '%m' "$file" 2>/dev/null || echo 0)"
  if [[ "$epoch" =~ ^[0-9]+$ ]] && [[ "$epoch" -gt 0 ]]; then
    date -u -r "$epoch" '+%Y-%m-%dT%H:%M:%SZ' 2>/dev/null || date -u '+%Y-%m-%dT%H:%M:%SZ'
  else
    echo ""
  fi
}

context_raw="$(wp eval 'echo wp_json_encode(get_option("lithia_project_context", array()));' --path="$ROOT" 2>&1 || true)"
context_json="$(printf '%s\n' "$context_raw" | extract_json)"
if [[ -z "$context_json" ]]; then
  context_json='{}'
fi

history_raw="$(wp eval '
$history = get_option("lithia_project_import_history", array());
$count = is_array($history) ? count($history) : 0;
echo wp_json_encode(array("import_history_count" => (int) $count));
' --path="$ROOT" 2>&1 || true)"
history_json="$(printf '%s\n' "$history_raw" | extract_json)"
if [[ -z "$history_json" ]]; then
  history_json='{"import_history_count":0}'
fi

events_tmp="$(mktemp)"
add_event() {
  local key="$1"
  local rel="$2"
  local abs="$EVID/$rel"
  if [[ -f "$abs" ]]; then
    local modified_at
    modified_at="$(mtime_iso "$abs")"
    jq -n \
      --arg key "$key" \
      --arg file "$rel" \
      --arg modified_at "$modified_at" \
      '{key:$key,file:$file,present:true,modified_at:$modified_at}' >> "$events_tmp"
  else
    jq -n \
      --arg key "$key" \
      --arg file "$rel" \
      '{key:$key,file:$file,present:false,modified_at:""}' >> "$events_tmp"
  fi
}

add_event "gate_check" "item-3d-v1-gate-check.json"
add_event "handoff_pack" "item-3e-release-candidate-handoff.md"
add_event "launch_transition" "item-3f-launch-state-transition.md"
add_event "deployment_state" "item-4a-deployment-state-check.json"
add_event "qa_checklist" "item-4b-qa-checklist-audit.json"

events_json="$(jq -s '.' "$events_tmp")"
missing_count="$(printf '%s' "$events_json" | jq '[.[] | select(.present==false)] | length')"

events_sorted="$(printf '%s' "$events_json" | jq 'sort_by(.modified_at)')"
review_state="$(printf '%s' "$context_json" | jq -r '.review_state // ""')"
source_state="$(printf '%s' "$context_json" | jq -r '.source_review_state // ""')"
history_count="$(printf '%s' "$history_json" | jq -r '.import_history_count // 0')"

jq -n \
  --arg runner "lithia-change-log-audit-4c" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --argjson context "$context_json" \
  --argjson history "$history_json" \
  --argjson events "$events_sorted" \
  --argjson missing "$missing_count" \
  --arg review_state "$review_state" \
  --arg source_state "$source_state" \
  --argjson history_count "$history_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    context: $context,
    import_history: $history,
    totals: {
      events: ($events | length),
      missing: $missing
    },
    events: $events,
    change_log: {
      review_state: $review_state,
      source_review_state: $source_state,
      import_history_count: $history_count
    },
    pass: (
      ($missing == 0)
      and ($review_state == "launched")
    )
  }'

rm -f "$events_tmp"
