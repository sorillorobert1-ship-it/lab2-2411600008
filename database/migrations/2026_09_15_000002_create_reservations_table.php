<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('transaction_type', ['booking', 'checkout']);
            $table->unsignedInteger('quantity');
            $table->string('reference_document', 150)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['room_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
