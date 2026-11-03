<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\ReplenishmentService;

class CheckReorderPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-reorder-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate purchase orders for products below their calculated reorder point';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count=0; $service=app(ReplenishmentService::class); $this->line('Checking active products against calculated reorder points...'); Product::with('suppliers')->where('is_active',true)->each(function($product)use($service,&$count){$supplier=$product->suppliers->first(); if($supplier){$point=$service->reorderPoint($product,$supplier); $this->line("- {$product->sku}: on hand {$product->quantity_on_hand}, reorder point {$point}");} if($service->generateFor($product))$count++;}); $this->info("Generated {$count} replenishment order(s)."); return self::SUCCESS;
    }
}
