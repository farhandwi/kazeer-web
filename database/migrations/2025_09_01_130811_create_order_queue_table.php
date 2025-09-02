<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderQueueTable extends Migration
{
    public function up()
    {
        Schema::create('order_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->integer('queue_number'); // Nomor antrian
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->integer('estimated_wait_time')->nullable(); // dalam menit
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'queue_number', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_queue');
    }
}