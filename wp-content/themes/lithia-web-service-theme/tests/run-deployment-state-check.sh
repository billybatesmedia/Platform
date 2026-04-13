#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
GATE_JSON="$EVID/item-3d-v1-gate-check.json"

extract_json() {
  awk 'BEGIN{emit=0} /^[[:space:]]*\{/ {emit=1} emit{print}'
}

raw_context="$(wp eval '
$context = get_option("lithia_project_context", array());
$payload_json = (string) get_option("lithia_project_payload_draft_json", "");
$payload_review_state = "";
if ("" !== trim($payload_json)) {
  $decoded = json_decode($payload_json, true);
  if (is_array($decoded)) {
    $payload_review_state = sanitize_key((string)($decoded["project"]["review_state"] ?? ""));
  }
}
$data = array(
  "site_url" => (string) home_url("/"),
  "site_name" => (string) get_option("blogname", ""),
  "review_state" => sanitize_key((string)($context["review_state"] ?? "")),
  "source_review_state" => sanitize_key((string)($context["source_review_state"] ?? "")),
  "payload_review_state" => $payload_review_state,
  "last_import_source" => sanitize_text_field((string)($context["last_import_source"] ?? "")),
  "last_imported_at" => sanitize_text_field((string)($context["last_imported_at"] ?? "")),
  "site_key" => sanitize_text_field((string)($context["site_key"] ?? "")),
  "template_key" => sanitize_text_field((string)($context["template_key"] ?? "")),
  "schema_version" => sanitize_text_field((string)($context["schema_version"] ?? "")),
  "payload_hash" => sanitize_text_field((string)($context["payload_hash"] ?? ""))
);
echo wp_json_encode($data, JSON_UNESCAPED_SLASHES);
' --path="$ROOT")"

context_json="$(printf '%s\n' "$raw_context" | extract_json)"
if [[ -z "$context_json" ]]; then
  jq -n --arg runner "lithia-deployment-state-check-4a" --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" --arg reason "failed to capture context json" '{runner:$runner,generated_at:$generated_at,pass:false,error:$reason}'
  exit 1
fi

if [[ -f "$GATE_JSON" ]]; then
  gate_ready="$(jq -r '.v1_ready // false' "$GATE_JSON" 2>/dev/null || echo false)"
  gate_failed_count="$(jq -r '.totals.failed // 999' "$GATE_JSON" 2>/dev/null || echo 999)"
else
  gate_ready="false"
  gate_failed_count="999"
fi

jq -n \
  --arg runner "lithia-deployment-state-check-4a" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --argjson context "$context_json" \
  --arg gate_ready "$gate_ready" \
  --argjson gate_failed "$gate_failed_count" \
  --arg gate_json_present "$( [[ -f "$GATE_JSON" ]] && echo true || echo false )" \
  --arg handoff_present "$( [[ -f "$EVID/item-3e-release-candidate-handoff.md" ]] && echo true || echo false )" \
  --arg launch_transition_present "$( [[ -f "$EVID/item-3f-launch-state-transition.md" ]] && echo true || echo false )" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    context: $context,
    artifacts: {
      gate_json_present: ($gate_json_present == "true"),
      handoff_present: ($handoff_present == "true"),
      launch_transition_present: ($launch_transition_present == "true")
    },
    gate: {
      v1_ready: ($gate_ready == "true"),
      failed_count: $gate_failed
    },
    deployment: {
      state_tracked: (($context.review_state // "") != ""),
      launched: (($context.review_state // "") == "launched"),
      consistent_payload_state: (
        (
          (($context.payload_review_state // "") == "approved")
          or (($context.payload_review_state // "") == "launched")
        )
        or (
          (($context.payload_review_state // "") == "")
          and (
            (($context.source_review_state // "") == "approved")
            or (($context.source_review_state // "") == "launched")
          )
        )
      )
    },
    pass: (
      (($gate_ready == "true"))
      and (($context.review_state // "") == "launched")
      and (($gate_json_present == "true"))
      and (($handoff_present == "true"))
      and (($launch_transition_present == "true"))
    )
  }'
