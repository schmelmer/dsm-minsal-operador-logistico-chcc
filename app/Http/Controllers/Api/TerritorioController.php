<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Region;
use App\Comuna;
use App\ServicioSalud;
use App\Establecimiento;

class TerritorioController extends Controller
{
    public function index()
    {
        return [
            [
                'type' => (new Region())->getMorphClass(),
                'name' => 'Region'
            ],
            [
                'type' => (new ServicioSalud())->getMorphClass(),
                'name' => 'Servicio de salud'
            ],
            [
                'type' => (new Comuna())->getMorphClass(),
                'name' => 'Municipio'
            ],
            [
                'type' => (new Establecimiento())->getMorphClass(),
                'name' => 'Establecimiento'
            ]
        ];
    }
}
