<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sys_orders_id');
            $table->foreign('sys_orders_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('resto_name');
            $table->string('menu_name');
            $table->decimal('price', 8, 2);
            $table->dateTime('date_order', 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_details');
    }
}
