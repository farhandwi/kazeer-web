<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            // Optimize for QR code functionality
            $table->index('table_code');
            $table->index(['restaurant_id', 'table_number']);
            
            // Add QR code metadata if needed
            if (!Schema::hasColumn('tables', 'qr_code_generated_at')) {
                $table->timestamp('qr_code_generated_at')->nullable();
            }
            
            if (!Schema::hasColumn('tables', 'qr_code_size')) {
                $table->integer('qr_code_size')->default(300);
            }
        });
    }

    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropIndex(['table_code']);
            $table->dropIndex(['restaurant_id', 'table_number']);
            
            if (Schema::hasColumn('tables', 'qr_code_generated_at')) {
                $table->dropColumn('qr_code_generated_at');
            }
            
            if (Schema::hasColumn('tables', 'qr_code_size')) {
                $table->dropColumn('qr_code_size');
            }
        });
    }
};