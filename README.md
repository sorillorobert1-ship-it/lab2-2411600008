# Laboratory Exercise 7

## Automated Inventory Replenishment and Supplier Management

This Laravel 12 application implements the requested Lab 7 module for a hardware inventory system.

### Run it

1. Open this folder in VS Code.
2. Run `php artisan migrate:fresh --seed`.
3. Run `php artisan serve` or point XAMPP Apache at the `public` folder.
4. Open `/` in a browser. Use `/demo-login` to sign in as the seeded manager (`manager@example.com` / `password`) before approving or receiving orders.

### Implemented requirements

- Supplier CRUD with `SoftDeletes`, lead times, reliability scores, and supplier catalogue.
- Product-to-supplier many-to-many relationship with agreed costs and minimum order quantities.
- Polymorphic purchase-order items and inventory transaction references.
- Reorder-point service using average daily usage, supplier lead time, and safety stock.
- `inventory:check-reorder-points` scheduled daily at 06:00.
- Purchase-order workflow: draft/pending approval/approved/received.
- Manager authorization through a Gate and `PurchaseOrderPolicy`.
- Queued-ready `PurchaseOrderStatusMail` and database/mail notification.
- Dashboard view composer with cached top-supplier metrics.
- Eager-loaded supplier and purchase-order queries.

The sample data contains a manager, one supplier, and two construction-material products. The SQLite database is used by default for easy evaluation; update `.env` for MySQL/XAMPP if required.

### Lab 6 integration

The Lab 7 project now preserves the Lab 6 inventory flow through `InventoryService`, `InventoryTransaction`, `StockChanged`, `NotifyLowStock`, `LowStockNotification`, `/inventory`, and `/api/inventory/live`. The compatibility migration adds the Lab 6 fields (`quantity`, `unit_price`, `transaction_type`, `quantity_before`, and `quantity_after`) while retaining the Lab 7 replenishment fields.
