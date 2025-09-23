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
        Schema::create('menu_option_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'Toping', 'Tingkat Pedas', 'Ukuran', 'Extra'
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('type', ['single', 'multiple']); // single = radio button, multiple = checkbox
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_option_categories');
    }
};
