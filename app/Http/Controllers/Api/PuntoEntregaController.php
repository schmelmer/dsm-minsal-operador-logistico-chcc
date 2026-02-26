<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\PuntoEntrega;
use Illuminate\Http\Request;
use App\Filters\PuntoEntregaFilter;
use App\Http\Controllers\ApiController;

class PuntoEntregaController extends ApiController
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index(PuntoEntregaFilter $filter)
    {
        $query = PuntoEntrega::with('region:id,name', 'servicio:id,name', 'comuna:id,name', 'establecimiento:id,name,hospital', 'responsable:id,name')->filter($filter);

        if (!request()->filled('page')) {
            return $this->respondIndex($query)->each(function ($punto) {
                $punto->append('tipo');
            });
        }

        return $this->respondIndex($query);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'calle'              => 'required|max:255',
            'numero'             => 'required|integer',
            'region_id'          => 'required',
            'servicio_id'        => 'required',
            'comuna_id'          => 'required',
            'establecimiento_id' => 'required_if:territorio_type,establecimiento',
            'user_id'            => 'required',
            'territorio_type'    => 'required',
        ], ['user_id.required'   => 'El campo responsable es obligatorio']);

        PuntoEntrega::create($request->all());

        $user = User::find($request->user_id);

        // $user->notify(new WelcomeNotification);

        return $this->respondStore();
    }

    public function show(PuntoEntrega $puntosEntrega)
    {
        $puntosEntrega->load('region');

        return $puntosEntrega;
    }

    public function update(Request $request, PuntoEntrega $puntosEntrega)
    {
        $this->validate($request, [
            'calle'              => 'sometimes|required|max:255',
            'numero'             => 'sometimes|required|integer',
            'region_id'          => 'sometimes|required',
            'servicio_id'        => 'sometimes|required',
            'comuna_id'          => 'sometimes|required',
            'establecimiento_id' => 'sometimes|required_if:territorio_type,establecimiento',
            'user_id'            => 'sometimes|required',
        ], ['user_id.required' => 'El campo responsable es obligatorio']);

        $puntosEntrega->fill($request->all())->save();

        return $this->respondUpdate();
    }

    public function destroy(PuntoEntrega $puntosEntrega, \App\Services\CalculaEstimacion $estimacion)
    {
        \Log::info(['Punto entrega eliminado' => $puntosEntrega->toArray()]);
        $puntosEntrega->delete();

        $estimacion->calculaProductos();

        return $this->respondDestroy('Eliminación exitosa y calculo de estimaciones finalizado!');
    }
}
