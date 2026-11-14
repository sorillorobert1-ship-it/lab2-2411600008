<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\User;
use App\Services\ReorderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckReorderPoints extends Command
{
    protected $signature = 'inventory:check-reorder';

    protected $aliases = ['inventory:check-reorder-points'];

    protected $description = 'Detect products below their reorder point and generate draft purchase orders';

    public function handle(ReorderService $reorderService): int
    {
        $this->info('Robert Hotel - automated reorder point check ('.now()->format('M d, Y h:i A').')');

        $products = $reorderService->getProductsNeedingReorder();

        if ($products->isEmpty()) {
            $this->info('No new purchase orders needed: all products are above their reorder points or already on an open purchase order.');

            return self::SUCCESS;
        }

        $this->table(
            ['SKU', 'Product', 'Supplier', 'On hand', 'Avg daily usage', 'Reorder point', 'Order qty'],
            $products->map(fn ($product) => [
                $product->sku,
                $product->name,
                $product->supplier->name,
                $product->quantity_on_hand,
                $reorderService->calculateAverageDailyUsage($product),
                $reorderService->calculateReorderPoint($product),
                $reorderService->calculateOptimalOrderQuantity($product),
            ])
        );

        $orders = $reorderService->generatePurchaseOrders();

        foreach ($orders as $order) {
            $this->line("  Created {$order->reference_number} for {$order->supplier->name}: {$order->items->count()} item(s), total PHP ".number_format($order->total, 2));
        }

        $admins = User::whereIn('role', ['admin', 'manager'])->get();

        if ($admins->isNotEmpty()) {
            try {
                Mail::to($admins)->send(new LowStockAlert($products));
                $this->info("Low stock alert sent to {$admins->count()} administrator(s).");
            } catch (\Throwable $e) {
                report($e);
                $this->warn('Purchase orders were created but the alert email could not be sent: '.$e->getMessage());
            }
        }

        $this->info("Generated {$orders->count()} purchase order(s) for {$products->count()} product(s).");

        return self::SUCCESS;
    }
}
