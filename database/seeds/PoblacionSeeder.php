<?php

use Illuminate\Database\Seeder;
use App\Poblacion;

class PoblacionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                "name" => "Poblacion Gestantes",
                "year" => 2017,
                "data" => json_decode(file_get_contents(database_path() . '/seeds/poblaciones/gestantes.json'), true)
            ],
            [
                "name" => "Población en control menor de 1 año",
                "year" => 2017,
                "data" => json_decode(file_get_contents(database_path() . '/seeds/poblaciones/menor_a_1_agno.json'), true)
            ],
            [
                "name" => "Población entre 1 y 2 años",
                "year" => 2017,
                "data" => json_decode(file_get_contents(database_path() . '/seeds/poblaciones/entre_1_a_2_agnos.json'), true)
            ],
            [
                "name" => "Población entre 2 y 3 años",
                "year" => 2017,
                "data" => json_decode(file_get_contents(database_path() . '/seeds/poblaciones/entre_2_a_3_agnos.json'), true)
            ]
        ];

        foreach ($data as $key => $value) {
            Poblacion::create($value);
        }
    }
}
