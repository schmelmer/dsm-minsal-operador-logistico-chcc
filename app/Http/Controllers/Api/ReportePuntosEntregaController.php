<?php

namespace App\Http\Controllers\Api;

use App\Comuna;
use App\Establecimiento;
use App\Http\Controllers\ApiController;
use App\Pedido;
use App\PuntoEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReportePuntosEntregaController extends ApiController
{
    public function index($agno, $sinPedidos = false)
    {
        // chequea resultados en cache
        /* $prefijoReporte = $sinPedidos ? 'sin' : 'con'; */
        /* $key = '_reporte_' . $prefijoReporte  . '_pedidos_' . $agno; */
        /* if (Cache::has($key)) { */
        /*     return Cache::get($key); */
        /* }; */

        $query = PuntoEntrega::with(
            'region:id,name',
            'servicio:id,name',
            'comuna:id,name',
            'establecimiento:id,name',
            'responsable:id,name,email,telefono'
        );

        if ($sinPedidos) {
            $query->doesntHave('pedidos');
        } else {
            $query->has('pedidos');
        }

        return $query->get()
            ->map(function ($punto) {
                return [
                    'region' => $punto->region->name,
                    'servicio' => $punto->servicio->name,
                    'comuna' => $punto->comuna->name,
                    'establecimiento' => isset($punto->establecimiento) ? $punto->establecimiento->name : null,
                    'solicitante' => $punto->responsable->name,
                    'correo' => $punto->responsable->email,
                    'telefono' => $punto->responsable->telefono,
                    'direccion' => $punto->direccion,
                ];
            });

        /* Cache::forever($key, $resultado); */
        /* return $resultado; */
    }
}
