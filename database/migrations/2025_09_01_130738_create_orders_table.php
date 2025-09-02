<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->enum('status', [
                'pending',      // Baru dibuat, belum dikonfirmasi
                'confirmed',    // Dikonfirmasi restoran
                'preparing',    // Sedang dimasak
                'ready',        // Siap disajikan
                'served',       // Sudah disajikan ke meja
                'completed',    // Selesai (customer puas)
                'cancelled'     // Dibatalkan
            ])->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'digital_wallet', 'transfer'])->nullable();
            $table->text('special_instructions')->nullable();
            $table->integer('estimated_prep_time')->nullable(); // total estimasi waktu masak
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}