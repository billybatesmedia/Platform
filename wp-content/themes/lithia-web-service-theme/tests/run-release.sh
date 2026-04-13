#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
OUT_4O="${2:-$ROOT/Docs/template-system/evidence/item-4o-full-release-sequence.json}"
OUT_4P="${3:-$ROOT/Docs/template-system/evidence/item-4p-evidence-integrity-snapshot.json}"
OUT_4Q="${4:-$ROOT/Docs/template-system/evidence/item-4q-doc-consistency-audit.json}"
OUT_4R="${5:-$ROOT/Docs/template-system/evidence/item-4r-release-status-board.json}"
OUT_4S="${6:-$ROOT/Docs/template-system/evidence/item-4s-release-artifact-index.json}"
OUT_4T="${7:-$ROOT/Docs/template-system/evidence/item-4t-release-attestation.json}"
TESTS_DIR="$ROOT/wp-content/themes/lithia-web-service-theme/tests"

mkdir -p "$(dirname "$OUT_4O")"
mkdir -p "$(dirname "$OUT_4P")"
mkdir -p "$(dirname "$OUT_4Q")"
mkdir -p "$(dirname "$OUT_4R")"
mkdir -p "$(dirname "$OUT_4S")"
mkdir -p "$(dirname "$OUT_4T")"

bash "$TESTS_DIR/run-full-release-sequence.sh" "$ROOT" > "$OUT_4O"
bash "$TESTS_DIR/run-evidence-integrity-snapshot.sh" "$ROOT" > "$OUT_4P"
bash "$TESTS_DIR/run-doc-consistency-audit.sh" "$ROOT" > "$OUT_4Q"
bash "$TESTS_DIR/run-release-status-board.sh" "$ROOT" > "$OUT_4R"
bash "$TESTS_DIR/run-release-artifact-index.sh" "$ROOT" > "$OUT_4S"
bash "$TESTS_DIR/run-release-attestation.sh" "$ROOT" > "$OUT_4T"

cat "$OUT_4T"
