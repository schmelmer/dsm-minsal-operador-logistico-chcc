<?php

use Illuminate\Database\Seeder;
use App\Producto;
use App\Categoria;
use App\Poblacion;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                "name" => "Acompañándote a Descubrir I",
                "categoria_id" => Categoria::whereName('Niños(as)')->first()->id,
                "poblacion_id" =>  Poblacion::whereName('Población en control menor de 1 año')->first()->id,
                "stock" => 170000,
                "year" => 2017,
                "cajas" => 30,
            ],
            [
                "name" => "Acompañándote a Descubrir II",
                "categoria_id" => Categoria::whereName('Niños(as)')->first()->id,
                "poblacion_id" =>  Poblacion::whereName('Población entre 1 y 2 años')->first()->id,
                "stock" => 140000,
                "year" => 2017,
                "cajas" => 30,
            ],
            [
                "name" => "Acompañándote a Descubrir III",
                "categoria_id" => Categoria::whereName('Gestantes')->first()->id,
                "poblacion_id" =>  Poblacion::whereName('Población entre 2 y 3 años')->first()->id,
                "stock" => 11000,
                "year" => 2017,
                "cajas" => 30,
            ],
            [
                "name" => "Guía de la gestación",
                "categoria_id" => Categoria::whereName('Gestantes')->first()->id,
                "poblacion_id" =>  Poblacion::whereName('Poblacion Gestantes')->first()->id,
                "stock" => 185000,
                "year" => 2017,
                "cajas" => 24,
            ],
            [
                "name" => "Guía de Paternidad Activa",
                "categoria_id" => Categoria::whereName('Padres')->first()->id,
                "poblacion_id" =>  Poblacion::whereName('Población en control menor de 1 año')->first()->id,
                "stock" => 150000,
                "year" => 2017,
                "cajas" => 60,
            ],
        ];

        foreach ($data as $key => $value) {
            Producto::firstOrCreate($value);
        }
    }
}
