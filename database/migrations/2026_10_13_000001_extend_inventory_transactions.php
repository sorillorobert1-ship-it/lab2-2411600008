<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void { Schema::table('inventory_transactions',function(Blueprint $table){
  $table->unsignedInteger('quantity_before')->nullable();$table->unsignedInteger('quantity_after')->nullable();$table->text('notes')->nullable();
 }); }
 public function down():void {Schema::table('inventory_transactions',function(Blueprint $table){$table->dropColumn(['quantity_before','quantity_after','notes']);});}
};
