# CSV to Canonical Payload Mapping

This table defines how CSV columns map to the canonical project payload.

## Global Rules

- Lists use `|` as delimiter in CSV.
- Booleans use `yes/no` or `true/false`.
- Keep `record_key` and `slug` stable for idempotent updates.
- Canonical naming is `service_*` (legacy `offer_*` is retired).

## project.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| schema_version | project.schema_version | yes | Usually `1.0.0` |
| template_key | project.template_key | yes | `service-business-v1` |
| industry | project.industry | yes | Example: `service-business` |
| site_key | project.site_key | yes | Stable site identifier |
| review_state | project.review_state | yes | `intake`, `approved`, `imported`, `qa`, `launched` |

## business.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| brand_name | business.brand_name | yes | |
| business_type | business.business_type | recommended | |
| short_tagline | business.short_tagline | recommended | |
| email | business.email | recommended | |
| phone | business.phone | recommended | |
| primary_cta_label | business.primary_cta_label | recommended | |
| primary_cta_target | business.primary_cta_target | recommended | URL/path |
| secondary_cta_label | business.secondary_cta_label | optional | |
| secondary_cta_target | business.secondary_cta_target | optional | URL/path |

## location.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| city | location.city | recommended | |
| state_region | location.state_region | recommended | |
| country | location.country | recommended | Example: `US` |
| service_area | location.service_area[] | optional | pipe list |
| delivery_modes | location.delivery_modes[] | optional | pipe list |

## seo.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| brand_keyword | seo.brand_keyword | recommended | |
| primary_terms | seo.primary_terms[] | optional | pipe list |
| secondary_terms | seo.secondary_terms[] | optional | pipe list |
| locations | seo.locations[] | optional | pipe list |
| tone | seo.tone | optional | |

## booking.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| booking_mode | booking.booking_mode | optional | |
| calendar_enabled | booking.calendar_enabled | optional | true/false |
| booking_notice | booking.booking_notice | optional | |

## providers.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| record_key | providers[].record_key | yes | Stable key |
| title | providers[].title | yes | |
| slug | providers[].slug | yes | unique |
| excerpt | providers[].excerpt | optional | |

## offers.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| record_key | offers[].record_key | yes | Stable key |
| title | offers[].title | yes | |
| slug | offers[].slug | yes | unique |
| summary | offers[].summary | recommended | |
| service_delivery_mode | offers[].service_delivery_mode | optional | |
| service_timeline | offers[].service_timeline | optional | |
| service_price_from | offers[].service_price_from | optional | Example: `$325` |
| service_price_notes | offers[].service_price_notes | optional | |
| service_audience | offers[].service_audience[] | optional | pipe list |
| service_outcomes | offers[].service_outcomes[] | optional | pipe list |
| service_primary_cta_label | offers[].service_primary_cta_label | optional | |
| service_primary_cta_url | offers[].service_primary_cta_url | optional | URL/path |
| service_secondary_cta_label | offers[].service_secondary_cta_label | optional | |
| service_secondary_cta_url | offers[].service_secondary_cta_url | optional | URL/path |
| provider_slugs | offers[].provider_slugs[] | optional | pipe list of provider slugs |
| service_homepage_spotlight_enabled | offers[].service_homepage_spotlight_enabled | optional | yes/no |
| service_homepage_spotlight_order | offers[].service_homepage_spotlight_order | optional | integer |
| rank_math_title | offers[].seo.title | optional | |
| rank_math_description | offers[].seo.description | optional | |
| rank_math_focus_keyword | offers[].seo.focus_keyword | optional | |

## pages.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| record_key | pages[].record_key | yes | Stable key |
| page_role | pages[].page_role | yes | unique (`home`, `about`, `contact`, `booking`, `platform`) |
| title | pages[].title | yes | |
| slug | pages[].slug | yes | unique |
| excerpt | pages[].excerpt | optional | |
| cta_label | pages[].cta_label | optional | |
| cta_target | pages[].cta_target | optional | URL/path |
| seo_title_seed | pages[].seo_title_seed | optional | |
| seo_description_seed | pages[].seo_description_seed | optional | |
| rank_math_title | pages[].seo.title | optional | |
| rank_math_description | pages[].seo.description | optional | |
| rank_math_focus_keyword | pages[].seo.focus_keyword | optional | |

## faq.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| question | faq[].question | yes | |
| answer_seed | faq[].answer_seed | yes | |

## proof.csv

| CSV column | Payload path | Required | Notes |
|---|---|---|---|
| years_experience | proof.years_experience | optional | |
| credentials | proof.credentials[] | optional | pipe list |
| highlights | proof.highlights[] | optional | pipe list |
| awards | proof.awards[] | optional | pipe list |
| testimonials | proof.testimonials[] | optional | Repeaters encoded as `name::quote::role::location` pipe list |
