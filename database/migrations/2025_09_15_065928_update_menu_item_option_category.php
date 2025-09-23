<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_item_option_categories', function (Blueprint $table) {
            // Hapus foreign key lama
            $table->dropForeign(['option_category_id']);
            $table->dropUnique(['menu_item_id', 'option_category_id']);

            // Ubah nama kolom
            $table->renameColumn('option_category_id', 'menu_option_category_id');

            // Tambahkan kembali foreign key dan unique constraint
            $table->foreign('menu_option_category_id')
                  ->references('id')
                  ->on('menu_option_categories')
                  ->onDelete('cascade');

            $table->unique(['menu_item_id', 'menu_option_category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_item_option_categories', function (Blueprint $table) {
            // Hapus foreign key baru
            $table->dropForeign(['menu_option_category_id']);
            $table->dropUnique(['menu_item_id', 'menu_option_category_id']);

            // Kembalikan nama kolom lama
            $table->renameColumn('menu_option_category_id', 'option_category_id');

            // Tambahkan kembali foreign key dan unique constraint lama
            $table->foreign('option_category_id')
                  ->references('id')
                  ->on('menu_option_categories')
                  ->onDelete('cascade');

            $table->unique(['menu_item_id', 'option_category_id']);
        });
    }
};
