<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->text('special_instructions')->nullable();
            $table->enum('status', [
                'pending',      // Belum mulai dimasak
                'preparing',    // Sedang dimasak
                'ready',        // Siap disajikan
                'served'        // Sudah disajikan
            ])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
