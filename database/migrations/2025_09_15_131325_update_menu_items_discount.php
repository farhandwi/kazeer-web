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
        Schema::table('menu_items', function (Blueprint $table) {
            // Kolom diskon
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('price');
            $table->decimal('discounted_price', 10, 2)->nullable()->after('discount_percentage');
            $table->datetime('discount_starts_at')->nullable()->after('discounted_price');
            $table->datetime('discount_ends_at')->nullable()->after('discount_starts_at');
            $table->boolean('is_on_discount')->default(false)->after('discount_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percentage',
                'discounted_price',
                'discount_starts_at',
                'discount_ends_at',
                'is_on_discount'
            ]);
        });
    }
};