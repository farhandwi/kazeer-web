<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            // Add indexes for better performance
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'table_number']);
            $table->unique(['restaurant_id', 'table_number']);
            $table->index('table_code');
        });
    }

    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'status']);
            $table->dropIndex(['restaurant_id', 'table_number']);
            $table->dropUnique(['restaurant_id', 'table_number']);
            $table->dropIndex(['table_code']);
        });
    }
};