<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('order_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_variant_id')->constrained()->onDelete('cascade');
            $table->decimal('price_modifier', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_item_variants');
    }
}
