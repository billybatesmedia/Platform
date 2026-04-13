#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
OUT_MD="$EVID/item-4p-evidence-integrity-snapshot-summary.md"

entries_tmp="$(mktemp)"
files_total=0
hash_failures=0

while IFS= read -r -d '' file; do
  rel="${file#$EVID/}"
  files_total=$((files_total + 1))

  size="$(wc -c < "$file" | tr -d ' ')"
  sha="$(openssl dgst -sha256 "$file" 2>/dev/null | awk '{print $NF}')"

  if [[ -z "$sha" ]]; then
    hash_failures=$((hash_failures + 1))
    sha="unavailable"
  fi

  modified_at="$(date -r "$file" -u +%Y-%m-%dT%H:%M:%SZ 2>/dev/null || echo "")"

  jq -n \
    --arg file "$rel" \
    --arg sha256 "$sha" \
    --argjson size "$size" \
    --arg modified_at "$modified_at" \
    '{file:$file,sha256:$sha256,size:$size,modified_at:$modified_at}' >> "$entries_tmp"
done < <(
  find "$EVID" -type f \
    ! -name 'item-4p-evidence-integrity-snapshot.json' \
    ! -name 'item-4p-evidence-integrity-snapshot-summary.md' \
    -print0 | sort -z
)

entries_json="$(jq -s '.' "$entries_tmp")"

anchor_4o_file="$EVID/item-4o-full-release-sequence.json"
anchor_4o_present=false
anchor_4o_pass=false
if [[ -f "$anchor_4o_file" ]]; then
  anchor_4o_present=true
  anchor_4o_pass="$(jq -r '.pass // false' "$anchor_4o_file" 2>/dev/null || echo false)"
fi

overall="false"
if [[ "$files_total" -gt 0 && "$hash_failures" -eq 0 && "$anchor_4o_present" == "true" && "$anchor_4o_pass" == "true" ]]; then
  overall="true"
fi

report_json="$(jq -n \
  --arg runner "lithia-evidence-integrity-snapshot-4p" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg root "$ROOT" \
  --arg evidence_dir "$EVID" \
  --arg anchor_4o_file "$anchor_4o_file" \
  --argjson anchor_4o_present "$anchor_4o_present" \
  --argjson anchor_4o_pass "$anchor_4o_pass" \
  --argjson files_total "$files_total" \
  --argjson hash_failures "$hash_failures" \
  --argjson entries "$entries_json" \
  --arg overall "$overall" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    root: $root,
    evidence_dir: $evidence_dir,
    anchors: {
      full_release_sequence_file: $anchor_4o_file,
      full_release_sequence_present: $anchor_4o_present,
      full_release_sequence_pass: $anchor_4o_pass
    },
    totals: {
      files: $files_total,
      hash_failures: $hash_failures
    },
    entries: $entries,
    pass: ($overall == "true")
  }')"

status_label="INCOMPLETE"
if [[ "$(printf '%s' "$report_json" | jq -r '.pass')" == "true" ]]; then
  status_label="COMPLETE"
fi

{
  echo "# Item 4.p Evidence Integrity Snapshot Summary"
  echo
  echo "Date: $(date -u +%Y-%m-%d)"
  echo "Generated at: $(printf '%s' "$report_json" | jq -r '.generated_at')"
  echo
  echo "## Final Status"
  echo
  echo "- Integrity snapshot status: **$status_label**"
  echo "- Files hashed: $(printf '%s' "$report_json" | jq -r '.totals.files')"
  echo "- Hash failures: $(printf '%s' "$report_json" | jq -r '.totals.hash_failures')"
  echo
  echo "## Anchors"
  echo
  echo "- Full release sequence file present: $(printf '%s' "$report_json" | jq -r '.anchors.full_release_sequence_present')"
  echo "- Full release sequence pass: $(printf '%s' "$report_json" | jq -r '.anchors.full_release_sequence_pass')"
  echo
  echo "## Artifact"
  echo
  echo "- item-4p-evidence-integrity-snapshot.json"
} > "$OUT_MD"

printf '%s\n' "$report_json"
rm -f "$entries_tmp"
