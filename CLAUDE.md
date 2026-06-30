# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A WordPress/WooCommerce plugin (PHP) that watermarks EPUB/MOBI/PDF ebooks through the
LemonInk cloud service. A store owner marks a downloadable product as "lemoninkable" and
assigns a LemonInk **master file ID**; on purchase the plugin creates a LemonInk
**transaction** that produces a per-buyer watermarked copy, and rewrites the customer's
download links to point at LemonInk's URLs.

The plugin talks to LemonInk via the `lemonink/lemonink-php` Composer package
(`LemonInk\Client`, `LemonInk\Models\Transaction`).

## Packaging

`src/` is a **self-contained plugin directory**: Composer's `vendor-dir` is set to
`src/vendor` (committed), so the mounted/shipped plugin carries its own dependencies.
The only runtime dependency is `lemonink/lemonink-php`; WooCommerce is supplied by the
host (or the dev environment), never bundled.

- **Build**: `bin/build` → `composer install --no-dev` then zips `src/` (+ `LICENSE`) to
  `tmp/lemonink.zip`.
- **Version**: `bin/bump-version major|minor|patch|<X.Y.Z>` sets the version everywhere it
  lives (plugin header, readme `Stable tag`, `package.json`).
- **Release**: `SVN_USERNAME=… SVN_PASSWORD=… bin/release major|minor|patch|<X.Y.Z>` — sets
  the version via `bin/bump-version`, builds, then commits/tags to the WordPress.org SVN
  repo. Credentials come from the environment (an untracked `.env`), never the script.

## Development & testing

Tooling is **WordPress-native**: [`wp-env`](https://www.npmjs.com/package/@wordpress/env)
(Docker) for the WP+WooCommerce stack, Playwright for browser e2e, and wp-cli smoke checks
for the compatibility matrix. Requires Docker and Node. Run `npm install` once.

Secrets live in an untracked `.env` (see `.env.example`): a LemonInk **test-mode**
`LEMONINK_API_KEY` and a `LEMONINK_MASTER_ID` that exists on that account.

- **Debug / iterate** (`src/` is mounted live — PHP edits need no rebuild):
  - `bin/dev` — start the store at `http://localhost:8888` (admin/`password`), WooCommerce
    + plugin activated. Pin WooCommerce with `WC_VERSION=9.0.0 bin/dev`.
  - `bin/seed` — set the API key and create a watermarked product in a reproducible state.
  - `bin/wp …` — wp-cli passthrough. `bin/logs [-f|--clear]` — the PHP debug log.
- **Compatibility matrix** (workflow 1): `bin/compat` boots each `(WP, WC)` pair in the
  matrix at the top of the script, runs `tests/support/wp/smoke.php` (real hooks + real
  LemonInk API), **fails a cell on any plugin PHP error in the debug log**, and prints a
  pass/fail table. Edit the `WP_VERSIONS`/`WC_VERSIONS` arrays to change coverage.
- **Full e2e** (workflow 1, current version): `bin/dev` then `bin/e2e` runs the Playwright
  suite in `tests/e2e/` — settings UI, product config, and the purchase → transaction →
  redirected download flow. `tests/support/wp/*.php` are wp-cli helpers (mapped into the
  container at `wp-content/lemonink-dev/`) used by both the smoke check and e2e setup.

There is intentionally **no PHPUnit suite**: the smoke checks exercise the plugin's logic
against real WP/WC, which is where version breakage shows up. Add one only if pure-helper
unit coverage becomes worth the WP test-scaffolding overhead.

## Architecture

`src/lemonink.php` is the entry point. On `plugins_loaded` it registers the integration
(settings screen); on `woocommerce_init` (`load_classes`) it loads `vendor/autoload.php`
and instantiates the four collaborator classes in `src/includes/`, passing each the
shared `WC_LemonInk_Integration` instance as `$settings`. Each class wires its own
WooCommerce action/filter hooks in its constructor, and most short-circuit (`return false`)
when the store isn't connected to LemonInk.

- **`WC_LemonInk_Integration`** — a `WC_Integration` (Settings → Integration → LemonInk).
  Stores/forgets the API key, exposes `$connected`, and is the single source of API
  clients via `get_api_client()` → `new LemonInk\Client($api_key)`. Always go through this
  to talk to LemonInk.
- **`WC_LemonInk_Product`** — admin product-edit UI. Adds the "Watermark downloads using
  LemonInk" checkbox + Master file ID field, persists them to post meta (`_li_lemoninkable`,
  `_li_master_id`), validates the master ID, and **generates the product's downloadable
  files** from the master's available formats (`generate_product_files`).
- **`WC_LemonInk_Order`** — purchase side. On `woocommerce_grant_product_download_access`
  it creates a LemonInk transaction and stores its id/token in order meta; it also handles
  the variation→parent transaction-inheritance case. Builds watermark params/values from
  order data (with email/name obfuscation helpers).
- **`WC_LemonInk_Download_Handler`** — forces `redirect` download method for LemonInk files
  and, on download, resolves the stored transaction to the watermarked file URL and redirects.
- **`WC_LemonInk_Order_Metadata`** — static helpers for reading/writing transaction data
  that abstract over **HPOS** (WooCommerce custom orders table) vs. legacy post meta.
  Always use these instead of `get_post_meta`/`wc_get_order` directly for transaction data.

### Key conventions

- **`_li_` prefix** identifies LemonInk-managed downloads and meta keys. Download IDs are
  `"_li_" . substr(md5(file), 4)`; `is_lemoninkable_download()` checks the prefix. This
  prefix is also how `fix_download_enabled` re-enables files that WooCommerce's "approved
  directories" feature would otherwise disable.
- **Master IDs are UUIDs.** Validation (`validate_master_file_is_present`) matches a UUID
  regex so WooCommerce's "file exists on disk" check passes for remote master files.
- A product's downloadable files are **derived from the master**, not uploaded — editing
  product meta triggers `generate_product_files`, which queries the master's formats.

## i18n

User-facing strings use the `lemonink` text domain. Translations live in `src/languages/`
(`.pot` template, `.po`/`.mo` per locale).
