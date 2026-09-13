# License & attribution report

## Summary

- **Project license:** GPL-3.0-only (`LICENSE`).
- **Inherited code:** WooCommerce Order Status Manager 1.15.7,
  © 2015–2025 SkyVerge, Inc. (info@skyverge.com), distributed under the
  GNU General Public License v3.0.
- The inherited code declares **GPL-3.0 only** (not "v2 or later"), therefore
  this distribution ships GPLv3 and **cannot** be relicensed as
  GPL-2.0-or-later. New contributions are GPL-3.0-only as well so the whole
  work stays distributable under GPLv3.

## Attribution obligations honored

- Every inherited source file keeps a header noting its origin ("a
  continuation of WooCommerce Order Status Manager 1.15.7"), the SkyVerge
  copyright line, and the license reference. SkyVerge is credited as original
  author; no false authorship is claimed.
- The upstream historical changelog is preserved verbatim in
  `changelog.txt`, including its SkyVerge attribution.
- The vendored SkyVerge WooCommerce Plugin Framework (`vendor/skyverge/`,
  version 5.15.9) is included unmodified with its own copyright headers and
  changelog. It is GPL-3.0 and remains © SkyVerge, Inc.

## Vendored / bundled third-party assets

| Component | Path | License | Source notice |
|---|---|---|---|
| SkyVerge WooCommerce Plugin Framework 5.15.9 | `vendor/skyverge/wc-plugin-framework/` | GPL-3.0 | headers in-tree |
| Composer autoloader | `vendor/composer/` | MIT | `vendor/composer/LICENSE` |
| Font Awesome (font + CSS) | `assets/fonts/`, `assets/css/font-awesome*.css` | Font Awesome license (OFL/MIT; font + CSS headers in-tree) | `http://fontawesome.io/license` (in file headers) |
| Magnific Popup | `assets/js/vendor/jquery.magnific-popup.min.js`, `assets/css/vendor/magnific-popup.css` | MIT | in file headers |
| fontIconPicker | `assets/js/vendor/jquery.fonticonpicker.min.js`, `assets/css/admin/jquery-fonticonpicker.min.css` | MIT | inherited from upstream build |
| WooCommerce icon font classes | `assets/css/woocommerce-font-classes*.css` | GPL (derived from woocommerce-icons) | header in file |

None of these components were modified beyond filename neutralization of the
plugin's own admin asset files.

## Removed commercial coupling

- WooCommerce.com updater header (`Woo:`) — already removed in the local
  fork; `Update URI: false` retained so WordPress never fetches upstream.
- `docs.woocommerce.com` and `woocommerce.com` product/support URLs replaced
  with neutral documentation in-repo or removed.
- No telemetry, no license activation, no remote HTTP requests exist in the
  runtime code paths (verified by source audit).

## How to comply when redistributing

Distribute the complete plugin directory, including `LICENSE`, the SkyVerge
copyright headers in source files, and this report. State your own changes
(CHANGELOG.md) and do not remove the original attribution.
