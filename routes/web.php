<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryTransactionController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/inventory', [InventoryController::class,'index'])->name('inventory.index');
    Route::post('/inventory/movements', [InventoryController::class,'store'])->name('inventory.store');
    Route::post('/inventory/adjustments', [InventoryController::class,'adjust'])->name('inventory.adjust');
    Route::get('/transactions', [InventoryTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [InventoryTransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [InventoryTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [InventoryTransactionController::class, 'show'])->name('transactions.show');
    Route::delete('/transactions/{transaction}', [InventoryTransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/api/inventory/live', [InventoryController::class,'api'])->name('inventory.api');
    Route::get('/api/dashboard/stats', [InventoryController::class,'dashboardStats'])->name('dashboard.stats');
    Route::get('/api/products/low-stock', [InventoryController::class,'lowStockProducts'])->name('products.low-stock');
    Route::get('/api/transactions/recent', [InventoryController::class,'recentTransactions'])->name('transactions.recent');
    Route::get('/api/inventory/notifications', [InventoryController::class,'notifications'])->name('inventory.notifications');
    Route::post('/inventory/notifications/read', [InventoryController::class,'markRead'])->name('inventory.notifications.read');
    Route::post('/inventory/notifications/{id}/read', [InventoryController::class,'markNotificationRead'])->name('inventory.notification.read');
    Route::get('/inventory/export', [InventoryController::class,'export'])->name('inventory.export');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports.index');
    Route::resource('rooms', RoomController::class);
    Route::resource('products', ProductController::class);
    Route::get('/products/export/csv', [ProductController::class, 'exportCsv'])->name('products.export.csv');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
