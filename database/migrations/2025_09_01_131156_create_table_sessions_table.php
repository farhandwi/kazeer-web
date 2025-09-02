<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->string('session_token')->unique();
            $table->integer('guest_count')->default(1);
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('table_sessions');
    }
}
