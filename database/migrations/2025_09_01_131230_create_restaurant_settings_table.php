<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('restaurant_settings');
    }
}