# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## bypasspermission
- bypasspermission

## Common commands

### Initial setup
- `composer setup`
  - Installs PHP dependencies
  - Creates `.env` if missing
  - Generates app key
  - Runs migrations
  - Installs frontend deps
  - Builds frontend assets

### Run app locally
- `composer dev`
  - Starts Laravel server, queue listener, log tail (`pail`), and Vite dev server concurrently
- `composer run dev:windows`
  - Starts Laravel server, queue listener, and Vite on Windows, where `pail` cannot run because PHP does not provide `pcntl`

### Frontend only
- `npm run dev` — start Vite dev server
- `npm run build` — production build

### Database
- `php artisan migrate`
- `php artisan migrate:fresh --seed`

### Tests
- `composer test` — clears config cache and runs full test suite
- `php artisan test` — run full suite directly
- `php artisan test --filter=<TestName>` — run a single test/method by filter
- `php artisan test tests/Feature/SomeFeatureTest.php` — run a single test file

### Code style
- `./vendor/bin/pint` — format code
- `./vendor/bin/pint --dirty` — format changed files only

### Useful app-specific commands
- `php artisan shop:low-stock-notify` — send low-stock admin notifications
- `php artisan app:apply-product-promotions` — apply active product promotions to product prices

## High-level architecture

This is a Laravel 13 monolith for a fashion e-commerce site with two major surfaces:

1. **Shop (customer-facing)** via `routes/web.php`
2. **Admin backoffice** via `routes/admin.php` (prefixed `/admin`, middleware `auth+verified+admin`)

### Request flow and layering
- **Controllers** under `app/Http/Controllers` handle HTTP orchestration.
- **Services** under `app/Services` contain business workflows (for example order and inventory workflows) and are used by controllers.
- **Eloquent models** under `app/Models` hold persistence + relationships.
- **Blade views** under `resources/views` render both shop and admin UI.

When adding non-trivial business logic, prefer extending/adding a Service class rather than embedding heavy logic in controllers.

### Core commerce domains
- **Catalog**: `Product`, `ProductVariant`, `Category`, `ProductImage`
  - Product variants carry operational stock and cost fields (`stock`, `avg_cost`, `last_cost`).
- **Cart/Checkout/Orders**: cart endpoints and checkout in shop controllers, order lifecycle in `OrderService`.
- **Discounting**:
  - Cart/checkout coupon flow (coupon entities and cart discount computation).
  - Product-level promotions via `ProductPromotion` and `app:apply-product-promotions` command.
- **Inventory receiving and valuation**:
  - Purchase receipts (`PurchaseReceipt`, `PurchaseReceiptItem`) + movement ledger (`InventoryMovement`).
  - Inventory updates and weighted-average cost updates live in `InventoryService`.

### Dashboard and reporting
- Admin dashboard controller aggregates key metrics directly from orders/order_items/product_variants:
  - revenue windows and daily revenue series
  - stock alerts and inventory totals
  - top-selling and slow-moving products
  - gross profit estimate using `avg_cost`

If you add metrics, keep query semantics consistent with existing order status rules used for revenue/profit.

### Import/export surface
- Product bulk import/export uses `maatwebsite/excel` (`App\Imports`, `App\Exports`).
- Order export currently includes CSV-style admin export endpoints.

### Frontend/build system
- Blade + Tailwind + Alpine + Vite.
- Vite entrypoints are configured through Laravel standard setup; use `@vite` in Blade layouts.

## Project notes from repository docs/rules scan
- No existing repository `CLAUDE.md` was found before this file.
- No `.cursorrules`, `.cursor/rules/*`, or `.github/copilot-instructions.md` were found.
- Top-level `README.md` is mostly Laravel framework boilerplate and does not define additional project-specific workflows beyond standard Laravel usage.
