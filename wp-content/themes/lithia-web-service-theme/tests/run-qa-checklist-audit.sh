#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-/Users/billybates/Local_Sites/service-site/app/public}"
THEME_TPL="$ROOT/wp-content/themes/lithia-web-service-theme/templates/front-page.html"

extract_first_url() {
  rg -o 'https?://[^[:space:]"'"'"'<>]+' -m1 || true
}

checks_tmp="$(mktemp)"

add_check() {
  local name="$1"
  local pass_flag="$2"
  local meta_json="$3"
  jq -n \
    --arg name "$name" \
    --argjson pass "$pass_flag" \
    --argjson meta "$meta_json" \
    '{name:$name,pass:$pass,meta:$meta}' >> "$checks_tmp"
}

base_raw="$(wp eval 'echo home_url("/");' --path="$ROOT" 2>&1 || true)"
BASE_URL="$(printf '%s\n' "$base_raw" | extract_first_url)"
if [[ -z "$BASE_URL" ]]; then
  BASE_URL="http://localhost:10004/"
fi
BASE_URL="${BASE_URL%/}"

service_raw="$(wp eval '
$ids = get_posts(array(
  "post_type" => "services",
  "post_status" => "publish",
  "posts_per_page" => 1,
  "fields" => "ids",
));
if (!empty($ids[0])) { echo get_permalink((int)$ids[0]); }
' --path="$ROOT" 2>&1 || true)"
SERVICE_URL="$(printf '%s\n' "$service_raw" | extract_first_url)"

check_http_ok() {
  local name="$1"
  local url="$2"
  local code
  code="$(curl -sS -L -o /dev/null -w '%{http_code}' "$url" || echo 0)"
  if [[ "$code" == "200" ]]; then
    add_check "$name" true "$(jq -n --arg url "$url" --argjson status "$code" '{url:$url,status:$status}')"
  else
    add_check "$name" false "$(jq -n --arg url "$url" --argjson status "$code" '{url:$url,status:$status}')"
  fi
}

check_http_ok "page_home_http_200" "$BASE_URL/"
check_http_ok "page_about_http_200" "$BASE_URL/about/"
check_http_ok "page_contact_http_200" "$BASE_URL/contact/"
check_http_ok "page_booking_http_200" "$BASE_URL/book-appointment/"

if [[ -n "$SERVICE_URL" ]]; then
  check_http_ok "page_service_http_200" "$SERVICE_URL"
else
  add_check "page_service_http_200" false '{"reason":"no published services permalink found"}'
fi

home_html="$(mktemp)"
curl -sS -L "$BASE_URL/" -o "$home_html" || true

markers=(
  "lithia-home-shell"
  "lithia-service-spotlight-loop"
  "lithia-mission-statement"
  "lithia-about-summary"
)

missing_markers=()
for marker in "${markers[@]}"; do
  if ! rg -q "$marker" "$home_html"; then
    missing_markers+=("$marker")
  fi
done

if [[ ${#missing_markers[@]} -eq 0 ]]; then
  add_check "homepage_core_markers_present" true '{"markers":["lithia-home-shell","lithia-service-spotlight-loop","lithia-mission-statement","lithia-about-summary"]}'
else
  missing_json="$(printf '%s\n' "${missing_markers[@]}" | jq -R . | jq -s .)"
  add_check "homepage_core_markers_present" false "$(jq -n --argjson missing "$missing_json" '{missing:$missing}')"
fi

headers="$(curl -sS -I -L "$BASE_URL/" || true)"
if printf '%s\n' "$headers" | rg -qi '^set-cookie:.*PHPSESSID'; then
  add_check "public_pages_no_phpsessid_cookie" false '{"reason":"PHPSESSID detected on homepage"}'
else
  add_check "public_pages_no_phpsessid_cookie" true '{}'
fi

if [[ -f "$THEME_TPL" ]]; then
  if rg -q 'lithia/business-hero|lithia/service-spotlight-loop' "$THEME_TPL"; then
    add_check "front_page_template_exists_and_has_sections" true "$(jq -n --arg path "$THEME_TPL" '{path:$path}')"
  else
    add_check "front_page_template_exists_and_has_sections" false "$(jq -n --arg path "$THEME_TPL" --arg reason "expected blocks not found" '{path:$path,reason:$reason}')"
  fi
else
  add_check "front_page_template_exists_and_has_sections" false "$(jq -n --arg path "$THEME_TPL" --arg reason "missing file" '{path:$path,reason:$reason}')"
fi

checks_json="$(jq -s '.' "$checks_tmp")"
failed_count="$(printf '%s' "$checks_json" | jq '[.[] | select(.pass==false)] | length')"
passed_count="$(printf '%s' "$checks_json" | jq '[.[] | select(.pass==true)] | length')"

jq -n \
  --arg runner "lithia-qa-checklist-audit-4b" \
  --arg generated_at "$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
  --arg base_url "$BASE_URL" \
  --arg service_url "$SERVICE_URL" \
  --argjson checks "$checks_json" \
  --argjson passed "$passed_count" \
  --argjson failed "$failed_count" \
  '{
    runner: $runner,
    generated_at: $generated_at,
    base_url: $base_url,
    service_url: $service_url,
    totals: {
      checks: ($passed + $failed),
      passed: $passed,
      failed: $failed
    },
    checks: $checks,
    pass: ($failed == 0)
  }'

rm -f "$checks_tmp" "$home_html"
