<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemStationsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_item_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('kitchen_station_id')->constrained()->onDelete('cascade');
            $table->integer('preparation_order')->default(1); // urutan jika ada multiple stations
            $table->timestamps();
            
            $table->unique(['menu_item_id', 'kitchen_station_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_item_stations');
    }
}