<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestoMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resto_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sys_resto_id');
            $table->foreign('sys_resto_id')->references('id')->on('resto')->onDelete('cascade');
            $table->string('name');
            $table->decimal('menu_price', 8, 2);
            $table->integer('status');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resto_menu');
    }
}
