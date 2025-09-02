<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('order_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_coupons');
    }
}
