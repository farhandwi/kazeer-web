<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('preparation_time')->default(15); // dalam menit
            $table->integer('sort_order')->default(0);
            $table->json('allergens')->nullable(); // array allergen
            $table->enum('spice_level', ['none', 'mild', 'medium', 'hot', 'very_hot'])->default('none');
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'slug']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
}