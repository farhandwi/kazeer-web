<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->string('name'); // contoh: "Size", "Spice Level", "Add-ons"
            $table->string('value'); // contoh: "Large", "Extra Spicy", "Extra Cheese"
            $table->decimal('price_modifier', 8, 2)->default(0); // tambahan/pengurangan harga
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_item_variants');
    }
}