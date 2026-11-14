# Laboratory Exercise 7

## Advanced MVC Architecture with Laravel: Automated Inventory Replenishment and Supplier Management

Laravel 12 application for **Robert Hotel's** room and guest-supplies inventory. It extends the Lab 6
real-time inventory tracking system with supplier management, automated reorder detection, a purchase
order approval workflow, email notifications, dashboard widgets, supplier performance reports, search
and CSV/PDF exports.

### Setup (XAMPP + VS Code)

1. Start **Apache** and **MySQL** in XAMPP and create a database named `lab7_inventory` in phpMyAdmin.
2. In the project folder run:
   ```bash
   composer install
   copy .env.example .env        # Windows (use cp on macOS/Linux)
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan serve --port=8001  # or double-click run-lab7.bat
   ```
3. Put your Mailtrap username/password in `.env` (`MAIL_USERNAME`, `MAIL_PASSWORD`).
4. Open http://127.0.0.1:8001 and sign in. All demo accounts use the password `password`:

| Role | Email | Can do |
|------|-------|--------|
| Admin | admin@roberthotel.com | Everything (incl. deleting draft POs) |
| Manager | manager@example.com | Manage suppliers, approve/reject/send POs, reports |
| Warehouse | warehouse@roberthotel.com | Receive deliveries, record stock movements |
| Staff | staff@roberthotel.com | Create and edit own draft POs |

Emails are sent immediately with `QUEUE_CONNECTION=sync`. To queue them instead set
`QUEUE_CONNECTION=database` and run `php artisan queue:work`.

### Scheduled reorder check

```bash
php artisan inventory:check-reorder   # run manually (screenshot 16)
php artisan schedule:list             # shows the daily 06:00 schedule
php artisan schedule:work             # runs the scheduler locally
```

Reorder Point = (Average Daily Usage x Lead Time) + Safety Stock. The suggested order quantity uses a
simplified EOQ, `sqrt((2 x D x S) / H)`, and never goes below the supplier's minimum order quantity.

### Features and where to find them

| Requirement | Implementation |
|-------------|----------------|
| Supplier model with SoftDeletes, scopes `active()` / `withLeadTime()`, accessors `full_contact` / `performance_rating` | `app/Models/Supplier.php` |
| Supplier CRUD with search, status filter, sort, pending-PO delete guard, restore | `SupplierController`, `resources/views/suppliers/*` |
| Purchase orders: statuses draft, pending, approved, rejected, sent, partially_received, received, cancelled | `app/Models/PurchaseOrder.php` |
| Line items with `line_total` and `outstanding_quantity` accessors | `app/Models/PurchaseOrderItem.php` |
| Create/edit with dynamic JavaScript line items, approve, reject, send, receive (partial), cancel, PDF | `PurchaseOrderController`, `resources/views/purchase-orders/*` |
| Approval history | `PurchaseOrderHistory` model, written by `PurchaseOrderObserver` |
| Policy (viewAny, view, create, update, delete, approve, receive) + Gates + `role` middleware | `app/Policies/PurchaseOrderPolicy.php`, `AppServiceProvider`, `app/Http/Middleware/EnsureUserHasRole.php` |
| Many-to-many product/supplier catalogue with agreed prices and MOQ | `product_supplier` table, `Product::suppliers()`, `Supplier::catalogProducts()` |
| Polymorphic relationship | `InventoryTransaction::reference()` (morphTo) / `PurchaseOrder::inventoryTransactions()` (morphMany) |
| ReorderService | `app/Services/ReorderService.php` |
| Artisan command + daily schedule | `app/Console/Commands/CheckReorderPoints.php`, `app/Console/Kernel.php`, `routes/console.php` |
| Mailables (queued): PurchaseOrderCreated (PDF attached), PurchaseOrderApproved, PurchaseOrderSent (PDF attached), LowStockAlert | `app/Mail/*`, `resources/views/emails/*` |
| Dashboard widgets via View Composers with caching | `app/Providers/ViewServiceProvider.php`, `resources/views/widgets/*` |
| Widget API endpoints (auto-refresh every 30s) | `GET /api/dashboard/{pending-orders,recent-orders,supplier-performance,reorder-alerts}` |
| Supplier performance: total orders, on-time, avg delivery vs quoted, accuracy, total value, charts, top/bottom | `SupplierPerformanceService`, `ReportController`, `/reports/supplier-performance` |
| Search service, `scopeFilter()` query scopes, reusable `<x-filter-form>` component | `app/Services/SearchService.php`, `resources/views/components/filter-form.blade.php` |
| CSV and PDF exports (products, purchase orders, transactions, supplier performance) | `app/Exports/*`, `ExportController` (uses `barryvdh/laravel-dompdf`) |
| Eager loading and query caching | `with()` / `withCount()` in controllers and services, `Cache::remember()` in composers and reports |

### Lab 6 integration

`InventoryService`, `InventoryTransaction`, the `StockChanged` event, the `NotifyLowStock` listener,
`LowStockNotification`, `/inventory` and `/api/inventory/live` are preserved. Receiving a purchase order
creates stock-in inventory transactions through `InventoryService`.

### Tests

```bash
php artisan test
```

Feature tests cover supplier CRUD and soft deletes, the purchase order workflow per role, email
notifications, receiving, reorder calculations, the scheduled command, dashboard APIs, performance
metrics, search filters and exports.
