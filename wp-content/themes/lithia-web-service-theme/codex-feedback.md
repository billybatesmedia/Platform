# Codex Feedback

## What I Changed

- Refactored the theme so design tokens are now driven by a native WordPress `Site Styles` page under `Appearance`.
- Added sensible default values for all requested token fields.
- Added sanitization for colors, rgba values, CSS lengths, numeric values, font-family stacks, font weights, and shadow values.
- Centralized token output as `:root` CSS custom properties with the `--lw-` prefix.
- Synced content and wide layout widths to both custom variables and WordPress global layout variables.
- Reduced `theme.json` to a minimal fallback role for block-theme support, layout defaults, and editor-friendly presets.
- Refactored the main theme styles and custom block styles to consume the new token variables instead of hardcoded global values wherever practical.
- Added reusable `.lw-section-light` and `.lw-section-dark` contrast utility classes.
- Added admin UI polish for the `Site Styles` screen, including grouped panels and a reset-to-defaults action.
- Kept the existing templates, template parts, JetEngine options-page logic, and custom blocks intact.

## Files Created

- `inc/site-styles.php`
- `assets/css/admin-site-styles.css`
- `assets/js/admin-site-styles.js`
- `codex-feedback.md`

## Files Updated

- `functions.php`
- `theme.json`
- `style.css`
- `inc/blocks.php`
- `assets/css/block-primitives.css`
- `assets/css/blocks-business-hero.css`
- `assets/css/blocks-brand-content.css`
- `assets/css/editor.css`

## Assumptions Made

- The active working theme is `lithia-web-service-theme` in the `service-site` Local install.
- A single grouped option (`lithia_site_styles`) is acceptable as the storage container as long as each token remains an individual field and setting value.
- Existing block markup should remain stable unless a small markup or inline-style adjustment was needed to move block styling onto token-driven CSS.
- Editor parity should be improved through shared variables and editor styles, but exact one-to-one parity may still depend on core editor canvas behavior.

## Anything Still Incomplete

- No external font-loading mechanism was added for the default heading/body stacks. The theme uses CSS font-family stacks only.
- The editor should now reflect the frontend much more closely, but some block editor chrome and iframe-specific spacing can still differ slightly from the public site.

## Risks Or Compatibility Notes

- The refactor relies on modern CSS features such as `color-mix()`. Current browsers handle this well, but very old browser support would be weaker.
- If a future change removes the `lithia-block-primitives` enqueue path, the dynamic `:root` variable output would also need to be reattached to another always-loaded stylesheet handle.
- Core/editor layout behavior still uses WordPress constrained layout rules, so extremely unusual custom block markup may need a follow-up pass if you want every layout edge case tokenized.

## Recommended Next Steps

- Populate `Appearance > Site Styles` with your preferred palette, typography, widths, and button tokens.
- Review the homepage and custom block sections in the editor after a hard refresh so the new variables load into the editor iframe.
- If you want deeper control next, the natural follow-up is adding more option-driven tokens for section backgrounds, hero overlays, and navigation/footer variants.
