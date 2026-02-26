<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('estima', function (App\Services\CalculaEstimacion $estimacion) {
    $this->comment('Calculando estimaciones ...');
    $estimacion->calculaProductos();

    $this->comment('Vaciando cache resultados ...');
    Cache::flush();
    $this->comment('Fin estimaciones');
});

Artisan::command('chcc', function (App\Services\CalculaEstimacion $estimacion) {
    $this->comment('Vaciando base de datos ...');
    $this->call('migrate:refresh');

    $this->comment('Importando base de datos ...');
    $this->call('db:seed', ['--class' => 'DatabaseSeeder']);

    $this->comment('Importando puntos de entrega ...');
    $this->call('chcc:importa');

    $this->comment('Calculando estimaciones ...');
    $estimacion->calculaProductos();

    $this->comment('Vaciando cache resultados ...');
    Cache::flush();

    $this->comment('Finalizado importacion de datos');
});

Artisan::command('hospital', function () {
    $this->comment('Actualizando columna hospital de establecimientos ...');
    Excel::load(database_path('seeds/territorios/establecimientos.xlsx'), function ($reader) {
        $sheet = $reader->get()->first();
        $sheet->each(function ($row) {
            $establecimiento = \App\Establecimiento::whereCode((int) $row->codigo_nuevo_establecimiento)->first();
            if ($row->nombre_oficial && $establecimiento && $row->hospital) {
                $establecimiento->hospital = strtolower($row->hospital);
                $establecimiento->save();
            }
        });
    });
    $this->comment('Fin.');
})->describe('Actualiza columna hospital de establecimiento');
