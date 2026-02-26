<?php

namespace App\Http\Controllers\Api;

use App\Comuna;
use App\Region;
use App\Producto;
use App\Estimacion;
use App\PuntoEntrega;
use App\ServicioSalud;
use App\Establecimiento;
use App\EstimacionManual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ApiController;

class ReporteStockController extends ApiController
{
    public function index($agno = 'Todos', $tipo = 'pais')
    {
        // chequea resultados en cache
        if (!request()->filled('producto_id')) {
            $key = '_reporte_stock_' . $agno . '_' . $tipo . '_' . request('categoria_id') . '_' . request('estado');
            if (Cache::has($key)) {
                return [
                    'data'            => Cache::get($key),
                    'productosFilter' => $this->getProductos(),
                ];
            };
        }

        $productos = $this->getProductos();

        $territorios = $this->getTerritorios($tipo);

        $pedidos =  $this->getPedidos($agno, $tipo);

        $estimaciones = $this->getEstimaciones($productos);

        $resultado = $this->getStock($territorios, $pedidos, $productos, $estimaciones, $tipo);

        // almacena resultados en cache ...
        if (!request()->filled('producto_id')) {
            Cache::forever($key, $resultado);
        }

        return [
            'data'            => $resultado,
            'productosFilter' => $productos
        ];
    }

    private function getProductos()
    {
        return Producto::select([
            'id',
            'name',
            'stock',
            'cajas',
            'stock',
            'de_servicios',
            'de_hospitales',
            'poblacion_id'
        ])
            ->when(request()->filled('producto_id'), function ($q) {
                return $q->whereIn('id', explode(',', request()->producto_id));
            })
            ->when(request()->filled('categoria_id'), function ($q) {
                return $q->where('categoria_id', request()->categoria_id);
            })
            ->when(request()->filled('estado'), function ($q) {
                return $q->where('estado', (bool) request()->estado);
            })
            ->orderBy('name')
            ->get();
    }

    private function getPedidos($agno, $tipo)
    {
        $query = DB::table('pedidos')->select(
            'id',
            'estado',
            'detalle',
            'punto_entrega_id'
        );

        if ($agno != 'Todos') {
            $query->whereAgno($agno);
        }

        if ($tipo != 'pais') {
            $query->addSubSelect($tipo . '_id', DB::table('punto_entregas')
                ->select($tipo . '_id')
                ->whereColumn('id', 'pedidos.punto_entrega_id')
                ->latest());
        }

        // castea atributo detalle a json para procesarlo en solicitudes ...
        return $query
            ->get()
            ->map(function ($p) {
                $p->detalle = json_decode($p->detalle, true);
                return $p;
            });
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
            $puntosEntregaEstablecimiento = PuntoEntrega::whereTerritorioType('establecimiento')->select('establecimiento_id')->pluck('establecimiento_id');
            return Establecimiento::whereIn('id', $puntosEntregaEstablecimiento)->getQuery()->get();
        } else {
            return collect([(object) ['id' => 'pais', 'name' => 'Pais']]);
        }
    }

    private function getEstimaciones($productos)
    {
        $estimaciones = Estimacion::select(
            'estimaciones.id',
            'estimaciones.producto_id',
            'estimaciones.poblacion_asignada',
            'estimaciones.cajas_total',
            'estimaciones.punto_entrega_id',
            'punto_entregas.region_id',
            'punto_entregas.servicio_id',
            'punto_entregas.comuna_id',
            'punto_entregas.establecimiento_id'
        )
            ->whereIn('producto_id', $productos->pluck('id'))
            ->join('punto_entregas', 'punto_entregas.id', 'punto_entrega_id')
            ->getQuery()
            ->get()
            ->groupBy(function ($estimacion) {
                return $estimacion->producto_id . "-" . $estimacion->punto_entrega_id;
            });

        $estimacionesManuales = EstimacionManual::select(
            'estimaciones_manuales.id',
            'estimaciones_manuales.producto_id',
            'estimaciones_manuales.cajas_manuales as cajas_total',
            'estimaciones_manuales.punto_entrega_id',
            'punto_entregas.region_id',
            'punto_entregas.servicio_id',
            'punto_entregas.comuna_id',
            'punto_entregas.establecimiento_id'
        )
            ->addSelect(DB::raw("0 as poblacion_asignada"))
            ->whereIn('producto_id', $productos->pluck('id'))
            ->join('punto_entregas', 'punto_entregas.id', 'punto_entrega_id')
            ->getQuery()
            ->get()
            ->groupBy(function ($estimacion) {
                return $estimacion->producto_id . "-" . $estimacion->punto_entrega_id;
            });

        return $estimaciones->merge($estimacionesManuales)->flatten(1);
    }

    private function getStock($territorios, $pedidos, $productos, $estimaciones, $tipo)
    {
        return $territorios->map(function ($territorio) use ($pedidos, $tipo, $productos, $estimaciones) {

            // filtra los pedidos de acuerdo al territorio que pertenecen ...
            if ($territorio->id != 'pais') {
                $pedidos = $pedidos->filter(function ($pedido) use ($tipo, $territorio) {
                    return isset($pedido->{$tipo . "_id"}) && $territorio->id == $pedido->{$tipo . "_id"};
                });
            }

            $estimacionesTerritorio = $estimaciones->whereIn('punto_entrega_id', $pedidos->pluck('punto_entrega_id'));

            $pedidosTerritorio = $pedidos->map(function ($pedido) {
                return [
                    'estado'           => $pedido->estado,
                    'detalle'          => $pedido->detalle,
                ];
            });

            $inventarioProductos = $productos->map(function ($producto) use ($pedidosTerritorio, $estimacionesTerritorio) {
                if ($pedidosTerritorio->isEmpty()) {
                    return [
                        'id'          => $producto->id,
                        'cajas'       => 0,
                        'asignados'   => 0,
                        'solicitados' => 0,
                        'poblacion'   => 0,
                    ];
                }

                $estimacion = $estimacionesTerritorio->where('producto_id', $producto->id);
                return [
                    'id'          => $producto->id,
                    'cajas'       => $producto->cajas,
                    'asignados'   => $estimacion->sum('cajas_total'),
                    'solicitados' => $pedidosTerritorio->pluck('detalle')->flatten(1)->where('producto_id', $producto->id)->sum('cantidad'),
                    'poblacion'   => $estimacion->sum('poblacion_asignada'),
                ];
            });

            return [
                'nombre'              => $territorio->name,
                'cantidadSolicitudes' => $pedidosTerritorio->count(),
                'enSolicitud'         => $pedidosTerritorio->where('estado', 'solicitud_realizada')->count(),
                'enTransito'          => $pedidosTerritorio->where('estado', 'en_transito')->count(),
                'enDestino'           => $pedidosTerritorio->where('estado', 'en_punto_destino')->count(),
                'productos'           => $inventarioProductos
            ];
        });
    }
}
