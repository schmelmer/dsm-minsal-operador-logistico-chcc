<?php

use Illuminate\Database\Seeder;
use App\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'Gestantes'],
            ['name' => 'Niños(as)'],
            ['name' => 'Padres'],
            ['name' => 'Profesionales'],
        ];


        foreach ($data as $value) {
            Categoria::firstOrCreate($value);
        }
    }
}
