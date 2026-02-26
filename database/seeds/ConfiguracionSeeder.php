<?php

use Illuminate\Database\Seeder;
use App\Configuracion;
use App\User;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuracion::firstOrCreate(['agno' => 2017, 'distribuidor' => User::whereName('TNT')->first()->id]);
    }
}
