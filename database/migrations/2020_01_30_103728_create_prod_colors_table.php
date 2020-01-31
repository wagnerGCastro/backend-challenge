<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProdColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prod_colors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_products')->unsigned();
            $table->foreign('id_products')->references('id')->on('products')->onDelete('cascade');
            $table->string('color_hexa', 9)->comment('code hexa, example: #00000000');
             $table->string('color_name', 60)->comment('description name color, example: black');
            $table->index(['id_products','color_name']);
            //$table->primary(['color_hexa']);
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
        Schema::dropIfExists('prod_colors');
    }
}
