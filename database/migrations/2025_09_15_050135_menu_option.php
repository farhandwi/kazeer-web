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
        Schema::create('menu_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_category_id')->constrained('menu_option_categories')->onDelete('cascade');
            $table->string('name'); // 'Extra Cheese', 'Pedas Level 1', 'Size Large'
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('additional_price', 10, 2)->default(0); // harga tambahan
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_options');
    }
};
