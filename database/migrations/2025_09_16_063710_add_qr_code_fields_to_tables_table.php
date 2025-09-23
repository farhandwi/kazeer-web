<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->string('table_code')->unique()->nullable()->after('table_number');
            $table->string('qr_code_path')->nullable()->after('table_code');
            
            // Remove old qr_code column if exists
            if (Schema::hasColumn('tables', 'qr_code')) {
                $table->dropColumn('qr_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['table_code', 'qr_code_path']);
            $table->string('qr_code')->nullable(); // Restore old column
        });
    }
};