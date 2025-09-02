<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // order_received, order_confirmed, order_ready, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // data tambahan
            $table->string('target_type'); // staff, customer, kitchen, all
            $table->unsignedBigInteger('target_id')->nullable(); // ID staff/customer tertentu
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['restaurant_id', 'target_type', 'is_read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
