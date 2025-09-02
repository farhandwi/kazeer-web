<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderStatusLogsTable extends Migration
{
    public function up()
    {
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->string('changed_by_type'); // staff, system, customer
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['order_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_status_logs');
    }
}
