<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/replenishment/check', [DashboardController::class, 'runReorderCheck'])->name('replenishment.check');

    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/pending-orders', [DashboardController::class, 'pendingOrders'])->name('pending-orders');
        Route::get('/recent-orders', [DashboardController::class, 'recentOrders'])->name('recent-orders');
        Route::get('/supplier-performance', [DashboardController::class, 'supplierPerformance'])->name('supplier-performance');
        Route::get('/reorder-alerts', [DashboardController::class, 'reorderAlerts'])->name('reorder-alerts');
    });

    Route::post('suppliers/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    Route::resource('suppliers', SupplierController::class);

    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::controller(PurchaseOrderController::class)->prefix('purchase-orders/{purchase_order}')->name('purchase-orders.')->group(function () {
        Route::post('/submit', 'submit')->name('submit');
        Route::post('/approve', 'approve')->name('approve');
        Route::post('/reject', 'reject')->name('reject');
        Route::post('/send', 'send')->name('send');
        Route::post('/receive', 'receive')->name('receive');
        Route::get('/pdf', 'pdf')->name('pdf');
    });

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/movements', [InventoryController::class, 'store'])->middleware('role:manager,warehouse')->name('inventory.store');
    Route::get('/api/inventory/live', [InventoryController::class, 'api'])->name('inventory.api');

    Route::get('/reports/supplier-performance', [ReportController::class, 'supplierPerformance'])
        ->middleware('role:manager')->name('reports.supplier-performance');

    Route::prefix('exports')->name('exports.')->whereIn('format', ['csv', 'pdf'])->group(function () {
        Route::get('/products/{format}', [ExportController::class, 'products'])->name('products');
        Route::get('/purchase-orders/{format}', [ExportController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/transactions/{format}', [ExportController::class, 'transactions'])->name('transactions');
        Route::get('/supplier-performance/{format}', [ExportController::class, 'supplierPerformance'])->middleware('role:manager')->name('supplier-performance');
    });
});
