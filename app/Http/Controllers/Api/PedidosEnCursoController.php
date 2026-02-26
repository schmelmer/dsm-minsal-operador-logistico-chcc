<?php

namespace App\Http\Controllers\Api;

use App\Pedido;
use App\Producto;
use App\Http\Controllers\ApiController;

class PedidosEnCursoController extends ApiController
{
    public function index($agno)
    {
        $productos = Producto::get()->map(function ($p) {
            return ['producto_id' => $p->id, 'cantidad' => 0];
        });

        return $this->getPedidos()
            // ->filter(function ($pedido) {
            //     return isset($pedido->puntoentrega);
            // })
            ->map(function ($pedido) use ($productos) {
                return [
                    'estado'                  => $pedido->estadoFormateado(),
                    'fechaEntrega'            => $pedido->fueEntregado() ? $pedido->updated_at->toDateString() : null,
                    'observacionDistribuidor' => $pedido->observacion_distribuidor,
                    'fechaSolicitud'          => $pedido->created_at->toDateString(),
                    'servicio'                => $pedido->puntoentrega->servicio->name,
                    'puntoEntrega'            => $pedido->puntoentrega->territorio->name,
                    'codigoTerritorio'        => $pedido->puntoentrega->territorio->code,
                    'solicitanteNombre'       => $pedido->puntoentrega->responsable->name,
                    'solicitanteEmail'        => $pedido->puntoentrega->responsable->email,
                    'solicitanteTelefono'     => $pedido->puntoentrega->responsable->telefono,
                    'productos'               => $productos->merge(collect($pedido->detalle))->pluck('cantidad', 'producto_id'),
                    'comuna'                  => $pedido->puntoentrega->comuna->name,
                    'direccion'               => $pedido->puntoentrega->calle,
                    'numero'                  => $pedido->puntoentrega->numero,
                    'folio'                   => $pedido->folio,
                    'observacionesCliente'    => $pedido->observacion,
                ];
            });

        // return [
        //     'total'    => $pedidos->total(),
        //     'data'     => $pedidosFormateados,
        // ];
    }

    private function getPedidos()
    {
        return Pedido::has('puntoentrega')
            ->with(
                'puntoentrega.responsable',
                'puntoentrega.servicio',
                'puntoentrega.comuna'
            )
            ->orderBy('created_at', 'desc')
            ->get();
        // ->paginate(100);
    }
}
