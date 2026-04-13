#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
REG_JSON="$EVID/item-3a-regression-tests.json"

extract_json() {
  awk 'BEGIN{emit=0} /^[[:space:]]*\{/ {emit=1} emit{print}'
}

payload_file=""
if [[ -f "$EVID/item-1a-approved-payload.json" ]]; then
  payload_file="$EVID/item-1a-approved-payload.json"
elif [[ -f "$EVID/item-2f-proof-payload.json" ]]; then
  payload_file="$EVID/item-2f-proof-payload.json"
fi

import_raw="$(
  LITHIA_RESYNC_PAYLOAD_FILE="$payload_file" wp eval '
  $draft_json = (string) get_option("lithia_project_payload_draft_json", "");
  $payload = json_decode($draft_json, true);
  $payload_source = "project_payload_draft_option";

  if (!is_array($payload) || empty($payload)) {
    $fallback = getenv("LITHIA_RESYNC_PAYLOAD_FILE");
    if (is_string($fallback) && "" !== trim($fallback) && file_exists($fallback)) {
      $raw = file_get_contents($fallback);
      $decoded = json_decode((string) $raw, true);
      if (is_array($decoded) && !empty($decoded)) {
        $payload = $decoded;
        $payload_source = "evidence_fallback_file";
      }
    }
  }

  if (!is_array($payload) || empty($payload)) {
    echo wp_json_encode(array(
      "runner" => "lithia-resync-readiness-check-4d",
      "error" => "no_valid_payload_available"
    ));
    return;
  }

  $report = lithia_import_project_payload(
    $payload,
    array(
      "dry_run" => true,
      "force"   => false,
      "source"  => "item_4d_resync_dry"
    )
  );

  $validation = isset($report["validation"]) && is_array($report["validation"]) ? $report["validation"] : array();
  $errors = isset($validation["errors"]) && is_array($validation["errors"]) ? $validation["errors"] : array();
  $warnings = isset($validation["warnings"]) && is_array($validation["warnings"]) ? $validation["warnings"] : array();

  echo wp_json_encode(array(
    "runner" => "lithia-resync-readiness-check-4d",
    "generated_at" => gmdate("c"),
    "payload_source" => $payload_source,
    "dry_run_success" => (bool) ($report["success"] ?? false),
    "summary" => $report["summary"] ?? array(),
    "context" => $report["context"] ?? array(),
    "validation" => array(
      "errors_count" => count($errors),
      "warnings_count" => count($warnings),
      "errors" => $errors
    )
  ));
  ' --path="$ROOT" 2>&1 || true
)"

import_json="$(printf '%s\n' "$import_raw" | extract_json)"
if [[ -z "$import_json" ]]; then
  import_json='{"runner":"lithia-resync-readiness-check-4d","error":"unable_to_parse_import_probe_json"}'
fi

lock_tests_pass=true
lock_missing=0
if [[ -f "$REG_JSON" ]]; then
  for t in \
    post_lock_flag_detected \
    import_respects_lock_flag_in_dry_run \
    managed_snapshot_blocks_mismatched_write \
    force_overrides_snapshot_guard \
    snapshot_allows_matching_write \
    review_state_blocks_non_dry_import_when_intake \
    review_state_allows_dry_import_when_intake
  do
    if ! jq -e --arg n "$t" '.tests[] | select(.name==$n and .pass==true)' "$REG_JSON" >/dev/null 2>&1; then
      lock_tests_pass=false
      lock_missing=$((lock_missing + 1))
    fi
  done
else
  lock_tests_pass=false
  lock_missing=7
fi

dry_ok="$(printf '%s' "$import_json" | jq -r '.dry_run_success // false')"
errors_count="$(printf '%s' "$import_json" | jq -r '.validation.errors_count // 999')"
source_state="$(printf '%s' "$import_json" | jq -r '.context.source_review_state // ""')"
review_state="$(printf '%s' "$import_json" | jq -r '.context.review_state // ""')"
live_context_raw="$(wp eval 'echo wp_json_encode(get_option("lithia_project_context", array()));' --path="$ROOT" 2>&1 || true)"
live_context_json="$(printf '%s\n' "$live_context_raw" | extract_json)"
if [[ -z "$live_context_json" ]]; then
  live_context_json='{}'
fi
live_review_state="$(printf '%s' "$live_context_json" | jq -r '.review_state // ""')"
live_source_state="$(printf '%s' "$live_context_json" | jq -r '.source_review_state // ""')"

jq -n \
  --arg runner "lithia-resync-readiness-check-4d" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg payload_file "$payload_file" \
  --argjson import_probe "$import_json" \
  --argjson live_context "$live_context_json" \
  --argjson lock_tests_pass "$lock_tests_pass" \
  --argjson lock_missing "$lock_missing" \
  --arg dry_ok "$dry_ok" \
  --argjson errors_count "$errors_count" \
  --arg source_state "$source_state" \
  --arg review_state "$review_state" \
  --arg live_review_state "$live_review_state" \
  --arg live_source_state "$live_source_state" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    payload_fallback_file: $payload_file,
    import_probe: $import_probe,
    live_context: $live_context,
    lock_guard_regression: {
      pass: $lock_tests_pass,
      missing_tests: $lock_missing
    },
    resync_readiness: {
      dry_run_success: ($dry_ok == "true"),
      validation_errors_zero: ($errors_count == 0),
      source_state_approved_or_launched: (($source_state == "approved") or ($source_state == "launched")),
      review_state_present: ($review_state != ""),
      live_review_state_launched: ($live_review_state == "launched"),
      live_source_state_approved_or_launched: (($live_source_state == "approved") or ($live_source_state == "launched"))
    },
    pass: (
      (($dry_ok == "true"))
      and ($errors_count == 0)
      and ((($source_state == "approved") or ($source_state == "launched")))
      and (($live_review_state == "launched"))
      and ((($live_source_state == "approved") or ($live_source_state == "launched")))
      and ($lock_tests_pass == true)
    )
  }'
