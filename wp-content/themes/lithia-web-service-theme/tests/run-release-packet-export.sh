#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
PACKETS_DIR="$ROOT/Docs/template-system/release-packets"

mkdir -p "$PACKETS_DIR"
stamp="$(date -u +%Y%m%d-%H%M%SZ)"
packet_dir="$PACKETS_DIR/$stamp"
mkdir -p "$packet_dir"

required=(
  "item-3e-release-candidate-handoff.md"
  "item-3f-launch-state-transition.md"
  "item-4f-release-readiness-suite.json"
  "item-4g-evidence-manifest-audit.json"
  "item-4h-release-handoff-record.json"
  "item-4i-final-certification-suite.json"
  "item-4j-v1-completion-certificate.json"
)

entries_tmp="$(mktemp)"
missing=0

for rel in "${required[@]}"; do
  src="$EVID/$rel"
  if [[ -f "$src" ]]; then
    cp "$src" "$packet_dir/$rel"
    sha="$(openssl dgst -sha256 "$packet_dir/$rel" 2>/dev/null | awk '{print $NF}')"
    size="$(wc -c < "$packet_dir/$rel" | tr -d ' ')"
    jq -n --arg file "$rel" --argjson present true --argjson size "$size" --arg sha256 "$sha" \
      '{file:$file,present:$present,size:$size,sha256:$sha256}' >> "$entries_tmp"
  else
    missing=$((missing + 1))
    jq -n --arg file "$rel" --argjson present false '{file:$file,present:$present,size:0,sha256:""}' >> "$entries_tmp"
  fi
done

entries_json="$(jq -s '.' "$entries_tmp")"

jq -n \
  --arg runner "lithia-release-packet-export-4k" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg packet_dir "$packet_dir" \
  --argjson entries "$entries_json" \
  --argjson missing "$missing" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    packet_dir: $packet_dir,
    totals: {
      files: ($entries | length),
      missing: $missing
    },
    entries: $entries,
    pass: ($missing == 0)
  }'

rm -f "$entries_tmp"
