<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Producto;
use App\Estimacion;
use App\EstimacionManual;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $user->load('puntosEntrega.pedidos');

        $productos = Producto::select(['id', 'name', 'de_servicios', 'de_hospitales'])->agnoActual()->whereEstado(true)->get();

        return $user->puntosEntrega->map(function ($puntoEntrega) use ($productos) {
            $pedidos = $puntoEntrega->pedidos->sortByDesc('id');
            $estimaciones = Estimacion::wherePuntoEntregaId($puntoEntrega->id)->whereIn('producto_id', $productos->pluck('id'))->get();
            $estimacionesManuales = EstimacionManual::wherePuntoEntregaId($puntoEntrega->id)->get();
            if ($estimacionesManuales) {
                $estimacionesManual = $estimacionesManuales->pluck('cajas_manuales', 'producto_id');
                $estimaciones->each(function ($estimacion) use ($estimacionesManual) {
                    $estimacion->cajas_total = isset($estimacionesManual[$estimacion->producto_id]) ? $estimacionesManual[$estimacion->producto_id] : $estimacion->cajas_total;
                });
            }
            $estimaciones = $estimaciones->pluck('cajas_total', 'producto_id');

            // agrega estimaciones manuales de productos de servicios y de hospitales, ya que estos productos no tienen estimaciones
            $productosDeServicios = $productos->filter(function ($p) {
                return $p->de_servicios || $p->de_hospitales;
            })->each(function ($p) use (&$estimaciones, $estimacionesManuales) {
                $estimaciones[$p->id] =  collect($estimacionesManuales->where('producto_id', $p->id)->first())->get('cajas_manuales', 0);
            });

            return [
                'punto'           => $puntoEntrega->tipo,
                'cantidadPedidos' => $pedidos->count(),
                'ultimoPedido'    => $pedidos->isEmpty() ? null : $pedidos->first()->created_at->diffForHumans(),
                'productos'       => $productos->filter(function ($producto) use ($puntoEntrega) {
                    if ($producto->de_servicios) {
                        return $puntoEntrega->territorio_type == 'servicio'
                            || $puntoEntrega->territorio_type == 'region';
                    }
                    if ($producto->de_hospitales) {
                        return $puntoEntrega->territorio->hospital;
                    }
                    return true;
                })->map(function ($producto) use ($pedidos, $estimaciones) {

                    $cajasPedidas = $pedidos->pluck('detalle')->flatten(1)->where('producto_id', $producto->id)->sum('cantidad');
                    $cajasTotal   = $estimaciones->get($producto->id, 0);

                    return [
                        'name'         => $producto->name,
                        'cajasPedidas' => $cajasPedidas,
                        'cajasTotal'   => $cajasTotal,
                        'porcentaje'   => $cajasTotal > 0 ? round($cajasPedidas / $cajasTotal, 2) * 100 : 0
                    ];
                })
            ];
        });
    }
}
