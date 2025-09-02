<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemStationsTable extends Migration
{
    public function up()
    {
        Schema::create('order_item_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('kitchen_station_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->integer('preparation_order');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_item_stations');
    }
}