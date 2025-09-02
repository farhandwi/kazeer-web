<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTimelineTable extends Migration
{
    public function up()
    {
        Schema::create('order_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // order_placed, confirmed, item_started, item_ready, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // data tambahan seperti item_id, station_id, etc.
            $table->timestamp('created_at');
            
            $table->index(['order_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_timeline');
    }
}