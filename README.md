# Open Custom Order Status for WooCommerce

Create and manage custom WooCommerce order statuses and order status emails.

This project is a **GPL community continuation** of the retired
**WooCommerce Order Status Manager 1.15.7** by SkyVerge, Inc. All inherited
code remains © SkyVerge, Inc. and licensed under the
[GNU General Public License v3.0](LICENSE); the original copyright and
attribution notices are preserved in the source files. New code is licensed
GPL-3.0-only as well.

## Features

- Custom order statuses with name, slug, description, color, and icon (font or image).
- Core status customization: labels, "paid" semantics, reports inclusion, bulk actions.
- "Next statuses" workflows: buttons on the orders screen and order preview modal.
- Custom email notifications dispatched on status transitions or new orders (HTML/plain).
- Download permissions, editable-order rules, and purchase notes respect paid flags.
- HPOS (High-Performance Order Storage) compatible.
- Read-only WP-CLI audit: `wp ocs-audit`.

## Requirements

| Component   | Minimum   | Tested up to |
|-------------|-----------|--------------|
| WordPress   | 5.6       | 6.9          |
| WooCommerce | 3.9.4     | 10.8.1       |
| PHP         | 7.4       | 8.5          |

## Installation

1. Copy the `open-custom-order-status` folder into `wp-content/plugins/`.
2. Activate it in the Plugins screen.
3. Manage statuses under **WooCommerce → Settings → Order Statuses**.

## Usage

- **Create/edit a status:** WooCommerce → Settings → Order Statuses → Add Order Status.
  The slug is a data identifier: do not rename slugs that are already in use by orders
  or referenced by other plugins.
- **Emails:** WooCommerce → Settings → Order Statuses → Emails. Each email is dispatched
  on configured transitions (`From → To`, `any`) or on new orders, and is configured in
  WooCommerce → Settings → Emails like any core email.
- **Diagnostics:** `wp ocs-audit` reports options, statuses, meta, emails, per-status
  order counts, scheduled actions, and legacy API references. It never writes to the DB.

## Documentation

- [Compatibility map](docs/compatibility.md) — legacy → new API, and what was kept.
- [Hook reference](docs/hooks.md) — actions, filters, AJAX, CLI.
- [Migration](docs/migration.md) — replacing the original plugin or a local fork.
- [Attribution & licenses](docs/attribution.md) — license obligations of inherited code.
- Historical upstream changelog: [changelog.txt](changelog.txt) (inherited, kept verbatim).

## Privacy

The plugin makes **no remote HTTP requests** and stores **no personal data beyond
WooCommerce's own order records**. There is no telemetry, no license activation, and no
update check (`Update URI: false`).

## Security

See [SECURITY.md](SECURITY.md). Report vulnerabilities responsibly; do not open public
issues for security reports.

## License

GPL-3.0-only. See [LICENSE](LICENSE) and [docs/attribution.md](docs/attribution.md).
