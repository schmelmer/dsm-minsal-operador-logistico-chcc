<?php

namespace App\Http\Controllers\Api;

use App\Pedido;
use App\Configuracion;
use Illuminate\Http\Request;
use App\Filters\PedidoFilter;
use App\Http\Controllers\ApiController;

class PedidoController extends ApiController
{
    public function index(PedidoFilter $filter)
    {
        $query = Pedido::with(
            'puntoentrega.responsable',
            'puntoentrega.comuna:id,name',
            'puntoentrega.servicio:id,name',
            'puntoentrega.establecimiento:id,name'
        )->orderBy('created_at', 'desc')
         ->filter($filter);

        return $this->respondIndex($query);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'puntoEntregaId'     => 'required',
            'productos'          => 'required|array',
            'productos.*.cantidad'  => 'required',
        ], ['productos.*.cantidad.required' => 'Ingrese cantidad del pedido']);

        $agnoActual = Configuracion::first()->agno;

        Pedido::create([
            'punto_entrega_id' => $request->puntoEntregaId,
            'detalle' => $request->productos,
            'agno' => $agnoActual,
            'observacion' => $request->observacion,
            'estado' => 'solicitud_realizada',
            'historial' => [[
                'estado' => 'solicitud_realizada',
                'fecha' => \Carbon\Carbon::now()->toDateTimeString()
            ]]
        ]);

        return $this->respondStore('Solicitud realizada!');
    }

    public function show(Pedido $pedido)
    {
        return $pedido;
    }

    public function update(Request $request, Pedido $pedido)
    {
        $this->validate($request, [
            'calle'              => 'sometimes|required|max:255',
            'numero'             => 'sometimes|required|integer',
            'user_id'            => 'sometimes|required',
            'file'               => 'sometimes|required|file',
        ]);

        // sube guia despacho
        if ($request->hasFile('file')) {
            $pedido->guia = $request->file->store(null, 'guias_disk');
        }

        // historial
        if ($pedido->estado != $request->estado) {
            $pedido->historial = collect($pedido->historial)->push([
                'estado' => $request->estado,
                'fecha' => \Carbon\Carbon::now()->toDateTimeString()
            ]);
        }

        if ($request->filled('productos')) {
            $pedido->detalle = $request->productos;
        }

        if ($request->filled('observacion')) {
            $pedido->observacion = $request->observacion;
        }

        if ($request->filled('punto_entrega_id' )) {
            $pedido->punto_entrega_id = $request->punto_entrega_id;
        }

        $pedido->fill([
            'estado' => $request->estado,
        ])->save();

        return $this->respondUpdate();
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->delete();

        return $this->respondDestroy();
    }

    public function rate(Request $request, Pedido $pedido)
    {
        $this->validate($request, [
            'rate'              => 'required|integer|min:1|max:7',
            'observacion'       => 'sometimes|max:5000',
        ]);

        if ($request->filled('observacion')) {
            $pedido->evaluacion_observacion = $request->observacion;
        }

        $pedido->fill([
            'evaluacion'   => $request->rate,
        ])->save();

        return $this->respondUpdate();
    }

    public function makeObservation(Request $request, Pedido $pedido)
    {
        $this->validate($request, [
            'observacion'       => 'required|max:5000',
        ]);

        $pedido->fill([
            'observacion_distribuidor'  => $request->observacion,
        ])->save();

        return $this->respondUpdate();
    }
}
