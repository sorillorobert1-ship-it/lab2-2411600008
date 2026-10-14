# Robert Hotel — Laboratory Exercise 6

This is an extension of the original Lab 5 project. Preserves login, rooms, products, reports, and existing styling.

## Setup on Windows / VS Code
1. BACK UP your Lab 5 folder and database first.
2. Extract this ZIP. Copy your own `.env` into the project (not included for security).
3. Run `composer install`. This Lab 6 version uses Blade and static CSS, so an npm build is not required.
4. Check `.env` database credentials; use the SAME existing Lab 5 database. Set `QUEUE_CONNECTION=database`.
5. Run `php artisan migrate` (NEVER use `migrate:fresh` on your existing data).
6. For a clean demonstration dataset, run `php artisan db:seed`.
7. In one terminal run `php artisan serve`; in another run `php artisan queue:work`.
8. Login and open `/inventory` or use Inventory Tracking in navigation.

## Features
- Authenticated stock in/out with quantity validation and DB row locks.
- Transaction audit trail with product, user, reference, before/after, timestamp.
- Eloquent relationships for Product, User, InventoryTransaction.
- StockChanged event and queued NotifyLowStock listener, database notifications.
- Authenticated JSON inventory API `/api/inventory/live`.
- AJAX refresh every 30 seconds on the Inventory page.
- Product creation logs initial stock; product edits cannot silently change stock.
- Historical transaction rows prevent deleting products with movements.

## Test cases / screenshots
1. Login, 2. Dashboard, 3. Inventory movement form, 4. Stock In, 5. Stock Out, 6. Insufficient stock validation, 7. Transaction history, 8. Database transaction rows, 9. Low stock notification, 10. JSON API, 11. Queue worker, 12. GitHub commit.

## Notes
- Existing inventory_transactions migration from Lab 5 is reused and extended, not duplicated.
- Low-stock notifications trigger when stock crosses from above to at/below the reorder level.
- MySQL supports row locking; SQLite has different locking behavior. For production-grade concurrency use MySQL.
- Laravel database queue worker must be running for alerts.
- This package uses Laravel Eloquent events/listeners for automatic updates; it does not install a separate SQL trigger or scheduled database event, to avoid double stock updates.
- Run `php artisan test` before submission. Also manually verify the browser flows against your own Lab 5 database.
