#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
EXPORT_JSON="$EVID/item-4k-release-packet-export.json"

if [[ ! -f "$EXPORT_JSON" ]]; then
  jq -n \
    --arg runner "lithia-release-packet-verify-4l" \
    --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    '{runner:$runner,generated_at:$generated_at,error:"missing item-4k-release-packet-export.json",pass:false}'
  exit 0
fi

packet_dir="$(jq -r '.packet_dir // ""' "$EXPORT_JSON")"
if [[ -z "$packet_dir" ]] || [[ ! -d "$packet_dir" ]]; then
  jq -n \
    --arg runner "lithia-release-packet-verify-4l" \
    --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
    --arg packet_dir "$packet_dir" \
    '{runner:$runner,generated_at:$generated_at,packet_dir:$packet_dir,error:"packet_dir missing",pass:false}'
  exit 0
fi

entries_tmp="$(mktemp)"
while IFS= read -r row; do
  file="$(printf '%s' "$row" | jq -r '.file')"
  expected_sha="$(printf '%s' "$row" | jq -r '.sha256 // ""')"
  abs="$packet_dir/$file"
  if [[ -f "$abs" ]]; then
    actual_sha="$(openssl dgst -sha256 "$abs" 2>/dev/null | awk '{print $NF}')"
    if [[ -z "$actual_sha" ]]; then
      actual_sha="unavailable"
    fi
    jq -n \
      --arg file "$file" \
      --arg expected_sha "$expected_sha" \
      --arg actual_sha "$actual_sha" \
      --argjson present true \
      --argjson sha_match "$( [[ "$expected_sha" == "$actual_sha" ]] && echo true || echo false )" \
      '{file:$file,present:$present,expected_sha256:$expected_sha,actual_sha256:$actual_sha,sha_match:$sha_match}' >> "$entries_tmp"
  else
    jq -n \
      --arg file "$file" \
      --arg expected_sha "$expected_sha" \
      --argjson present false \
      '{file:$file,present:$present,expected_sha256:$expected_sha,actual_sha256:"",sha_match:false}' >> "$entries_tmp"
  fi
done < <(jq -c '.entries[]' "$EXPORT_JSON")

entries_json="$(jq -s '.' "$entries_tmp")"
missing_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.present==false)] | length')"
mismatch_count="$(printf '%s' "$entries_json" | jq '[.[] | select(.sha_match==false)] | length')"

jq -n \
  --arg runner "lithia-release-packet-verify-4l" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg export_file "$EXPORT_JSON" \
  --arg packet_dir "$packet_dir" \
  --argjson entries "$entries_json" \
  --argjson missing "$missing_count" \
  --argjson mismatch "$mismatch_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    export_file: $export_file,
    packet_dir: $packet_dir,
    totals: {
      files: ($entries | length),
      missing: $missing,
      checksum_mismatch: $mismatch
    },
    entries: $entries,
    pass: ($missing == 0 and $mismatch == 0)
  }'

rm -f "$entries_tmp"
