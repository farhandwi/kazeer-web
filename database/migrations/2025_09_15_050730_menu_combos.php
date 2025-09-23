<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_combos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('combo_price', 10, 2);
            $table->decimal('original_price', 10, 2); // total harga jika beli terpisah
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('preparation_time')->default(20);
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_combos');
    }
};
