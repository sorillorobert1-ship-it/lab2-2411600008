<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
            Schema::create('inventory_transactions_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->enum('transaction_type', ['stock_in', 'stock_out', 'adjustment']);
                $table->unsignedInteger('quantity');
                $table->string('reference_document', 150)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();
                $table->unsignedInteger('quantity_before')->nullable();
                $table->unsignedInteger('quantity_after')->nullable();
                $table->text('notes')->nullable();
                $table->index(['product_id', 'transaction_type']);
            });
            DB::statement('INSERT INTO inventory_transactions_new (id, product_id, transaction_type, quantity, reference_document, user_id, created_at, quantity_before, quantity_after, notes) SELECT id, product_id, transaction_type, quantity, reference_document, user_id, created_at, quantity_before, quantity_after, notes FROM inventory_transactions');
            Schema::drop('inventory_transactions');
            Schema::rename('inventory_transactions_new', 'inventory_transactions');
            DB::statement('PRAGMA foreign_keys=ON');
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY transaction_type ENUM('stock_in','stock_out','adjustment') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY transaction_type ENUM('stock_in','stock_out') NOT NULL");
        }
    }
};
