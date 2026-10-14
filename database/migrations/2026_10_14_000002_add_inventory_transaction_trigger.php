<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_after_insert');
        DB::unprepared('CREATE TRIGGER inventory_transactions_after_insert AFTER INSERT ON inventory_transactions FOR EACH ROW UPDATE products SET quantity = NEW.quantity_after, updated_at = CURRENT_TIMESTAMP WHERE id = NEW.product_id');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_transactions_after_insert');
        }
    }
};
