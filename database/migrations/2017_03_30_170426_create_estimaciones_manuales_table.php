<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimacionesManualesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimaciones_manuales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('punto_entrega_id');
            $table->unsignedInteger('producto_id');
            $table->integer('cajas_manuales');
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
        Schema::drop('estimaciones_manuales');
    }
}
