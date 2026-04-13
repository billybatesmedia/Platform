#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
EVID="$ROOT/Docs/template-system/evidence"
OUT_MD="$EVID/item-4n-release-closeout-report.md"
OUT_JSON="$EVID/item-4n-release-closeout-report.json"

gate_file="$EVID/item-3d-v1-gate-check.json"
final_cert_file="$EVID/item-4i-final-certification-suite.json"
completion_file="$EVID/item-4j-v1-completion-certificate.json"
timeline_file="$EVID/item-4m-release-timeline-audit.json"

gate_pass="$(jq -r '.v1_ready // false' "$gate_file" 2>/dev/null || echo false)"
final_pass="$(jq -r '.pass // false' "$final_cert_file" 2>/dev/null || echo false)"
complete_pass="$(jq -r '.v1_complete // false' "$completion_file" 2>/dev/null || echo false)"
timeline_pass="$(jq -r '.pass // false' "$timeline_file" 2>/dev/null || echo false)"

clean_last_line() {
  awk 'NF{line=$0} END{print line}'
}

review_state_raw="$(wp eval '$c=get_option("lithia_project_context", array()); echo is_array($c) ? (string)($c["review_state"] ?? "") : "";' --path="$ROOT" 2>/dev/null || true)"
source_state_raw="$(wp eval '$c=get_option("lithia_project_context", array()); echo is_array($c) ? (string)($c["source_review_state"] ?? "") : "";' --path="$ROOT" 2>/dev/null || true)"
review_state="$(printf '%s\n' "$review_state_raw" | clean_last_line)"
source_state="$(printf '%s\n' "$source_state_raw" | clean_last_line)"

overall="false"
if [[ "$gate_pass" == "true" && "$final_pass" == "true" && "$complete_pass" == "true" && "$timeline_pass" == "true" && "$review_state" == "launched" ]]; then
  overall="true"
fi

jq -n \
  --arg runner "lithia-release-closeout-report-4n" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg gate_pass "$gate_pass" \
  --arg final_pass "$final_pass" \
  --arg complete_pass "$complete_pass" \
  --arg timeline_pass "$timeline_pass" \
  --arg review_state "$review_state" \
  --arg source_state "$source_state" \
  --arg overall "$overall" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    checks: {
      v1_gate_ready: ($gate_pass == "true"),
      final_certification_pass: ($final_pass == "true"),
      v1_completion_true: ($complete_pass == "true"),
      release_timeline_pass: ($timeline_pass == "true"),
      review_state_launched: ($review_state == "launched"),
      source_review_state_approved_or_launched: (($source_state == "approved") or ($source_state == "launched"))
    },
    pass: ($overall == "true")
  }' > "$OUT_JSON"

status_label="INCOMPLETE"
if [[ "$overall" == "true" ]]; then
  status_label="COMPLETE"
fi

cat > "$OUT_MD" <<EOF
# Item 4.n Release Closeout Report

Date: $(date -u +%Y-%m-%d)
Generated at: $(jq -r '.generated_at' "$OUT_JSON")

## Final Status

- Release status: **$status_label**

## Checks

- V1 gate ready (\`3.d\`): \`$gate_pass\`
- Final certification pass (\`4.i\`): \`$final_pass\`
- V1 completion certificate (\`4.j\`): \`$complete_pass\`
- Release timeline audit (\`4.m\`): \`$timeline_pass\`
- Live review state: \`$review_state\`
- Source review state: \`$source_state\`

## Artifacts

- [item-3d-v1-gate-check.json]($gate_file)
- [item-4i-final-certification-suite.json]($final_cert_file)
- [item-4j-v1-completion-certificate.json]($completion_file)
- [item-4m-release-timeline-audit.json]($timeline_file)
- [item-4n-release-closeout-report.json]($OUT_JSON)
EOF

cat "$OUT_JSON"
