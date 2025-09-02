<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablesTable extends Migration
{
    public function up()
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('table_number');
            $table->string('qr_code')->unique(); // QR code unik untuk setiap meja
            $table->integer('capacity');
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['restaurant_id', 'table_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tables');
    }
}
