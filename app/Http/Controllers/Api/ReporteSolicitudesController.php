<?php

namespace App\Http\Controllers\Api;

use App\Comuna;
use App\Pedido;
use App\Region;
use App\Producto;
use App\PuntoEntrega;
use App\ServicioSalud;
use App\Establecimiento;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ApiController;

class ReporteSolicitudesController extends ApiController
{
    public function index($agno, $tipo = 'pais')
    {
        // chequea resultados en cache
        // $key = '_reporte_solicitudes_' . $agno . '_' . $tipo . '_' request(-);
        // if (Cache::has($key)) {
        //     return Cache::get($key);
        // };

        $productos = Producto::select(['id', 'name', 'stock', 'cajas'])
            ->when(request()->filled('producto_id'), function ($q) {
                return $q->whereIn('id', explode(',', request()->producto_id));
            })
            ->when(request()->filled('categoria_id'), function ($q) {
                return $q->where('categoria_id', request()->categoria_id);
            })
            ->when(request()->filled('estado'), function ($q) {
                return $q->where('estado', (boolean)request()->estado);
            })
            ->agnoActual()
            ->get();

        $pedidos =  $this->getPedidos($agno);

        $territorios = $this->getTerritorios($tipo);

        $resultado = $territorios->map(function ($territorio) use ($pedidos, $tipo, $productos) {

            if ($territorio->id != 'pais') {
                $pedidos = $pedidos->filter(function ($pedido) use ($tipo, $territorio) {
                    return isset($pedido->puntoentrega) && $territorio->id == $pedido->puntoentrega->{$tipo . "_id"};
                });
            }

            $productosTerritorio = $pedidos->map(function ($pedido) {
                return [
                    'estado'  => $pedido->estado,
                    'detalle' => $pedido->detalle
                ];
            });

            $inventarioProductos = $productos->map(function ($producto) use ($productosTerritorio) {
                if ($productosTerritorio->isEmpty()) {
                    return [
                        'id'    => $producto->id,
                        'valor' => 0
                    ];
                }
                return [
                    'id'    => $producto->id,
                    'valor' => $productosTerritorio->pluck('detalle')->flatten(1)->where('producto_id', $producto->id)->sum('cantidad')
                ];
            });

            return [
                'nombre'              => $territorio->name,
                'cantidadSolicitudes' => $productosTerritorio->count(),
                'enSolicitud'         => $productosTerritorio->where('estado', 'solicitud_realizada')->count(),
                'enTransito'          => $productosTerritorio->where('estado', 'en_transito')->count(),
                'enDestino'           => $productosTerritorio->where('estado', 'en_punto_destino')->count(),
                'productos'           => $inventarioProductos->pluck('valor', 'id'),
                // 'productosColumnas'   => $productos,
            ];
        });

        // Cache::forever($key, $resultado);

        // return $resultado;
        return [
            'data'            => $resultado,
            'productosFilter' => $productos
        ];
    }

    private function getPedidos($agno)
    {
        return Pedido::whereAgno($agno)
            ->with('puntoentrega')
            ->get();
    }

    private function getTerritorios($tipo)
    {
        if ($tipo == 'region') {
            return Region::getQuery()->get();
        } else if ($tipo == 'comuna') {
            return Comuna::getQuery()->get();
        } else if ($tipo == 'servicio') {
            return ServicioSalud::getQuery()->get();
        } else if ($tipo == 'establecimiento') {
            $puntosEntregaEstablecimiento = PuntoEntrega::whereTerritorioType('establecimiento')->select('establecimiento_id')->get()->pluck('establecimiento_id');
            return Establecimiento::whereIn('id', $puntosEntregaEstablecimiento)->getQuery()->get();
        } else {
            return collect([(object)['id' => 'pais', 'name' => 'Pais']]);
        }
    }

    private function getPuntosEntrega($tipo)
    {
        return PuntoEntrega::whereTerritorioType($tipo)
            ->getQuery()
            ->get();
    }
}
