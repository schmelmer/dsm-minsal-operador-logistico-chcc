<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaColumnaPoblacionAsignadaATablaEstimaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimaciones', function (Blueprint $table) {
            $table->integer('poblacion_asignada')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimaciones', function (Blueprint $table) {
            $table->dropColumn('poblacion_asignada');
        });
    }
}
