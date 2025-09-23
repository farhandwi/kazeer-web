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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 10, 2);
            $table->decimal('minimum_order', 10, 2)->nullable();
            $table->decimal('maximum_discount', 10, 2)->nullable();
            
            // Masa berlaku
            $table->datetime('starts_at');
            $table->datetime('expires_at');
            
            // Batasan penggunaan
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_customer')->nullable();
            $table->integer('used_count')->default(0);
            
            // Target aplikasi diskon
            $table->json('applicable_restaurants')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_menu_items')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->enum('customer_eligibility', ['all', 'new_customers', 'returning_customers'])->default('all');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
