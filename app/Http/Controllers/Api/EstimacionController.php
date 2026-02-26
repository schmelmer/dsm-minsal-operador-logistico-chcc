<?php

namespace App\Http\Controllers\Api;

use App\Producto;
use App\Estimacion;
use App\PuntoEntrega;
use App\EstimacionManual;
use Illuminate\Http\Request;
use App\Filters\EstimacionFilter;
use App\Http\Controllers\ApiController;

class EstimacionController extends ApiController
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index(EstimacionFilter $filter)
    {
        $puntoEntrega = PuntoEntrega::with('territorio')->find(request()->puntoEntregaId);
        $productos = Producto::whereEstado(true)->get()->map(function ($p) use ($puntoEntrega) {
            return [
                'producto_id' => $p->id,
                'punto_entrega_id' => request()->puntoEntregaId,
                'producto' => $p,
                'puntoentrega' => $puntoEntrega
            ];
        });

        $estimaciones = Estimacion::with(
            'producto:id,name,poblacion_id',
            'producto.poblacion:id,name,year',
            'puntoentrega'
        )->filter($filter)->get();

        $estimacionesManuales = EstimacionManual::with(
            'producto:id,name',
            'puntoentrega'
        )->wherePuntoEntregaId(request()->puntoEntregaId)->get();

        $estimacionesManuales->each(function ($estimacionManual) use ($estimaciones) {
            $estimacion = $estimaciones->first(function ($estimacion) use ($estimacionManual) {
                return $estimacion->punto_entrega_id == $estimacionManual->punto_entrega_id &&
                    $estimacion->producto_id == $estimacionManual->producto_id;
            });
            if ($estimacion) {
                $estimacion->cajas_total = $estimacionManual->cajas_manuales;
            } else {
                $estimacionManual->cajas_total = $estimacionManual->cajas_manuales;
                $estimaciones->push($estimacionManual);
            }
        });

        // mezcla todos los productos con las estimaciones
        $filtrados = $productos->filter(function ($p) use ($puntoEntrega) {
            if ($p['producto']->de_servicios) {
                return $puntoEntrega->territorio_type == 'servicio' || $puntoEntrega->territorio_type == 'region';
            }
            if ($p['producto']->de_hospitales) {
                return $puntoEntrega->establecimiento && $puntoEntrega->establecimiento->hospital;
            }
            return true;
        })->filter(function ($p) use ($estimaciones) {
            return ! $estimaciones->contains('producto_id', $p['producto_id']);
        });

        return $filtrados->merge($estimaciones);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'valor'            => 'required|integer',
            'total'            => 'required|integer',
            'punto_entrega_id' => 'required',
            'producto_id'      => 'required',
        ]);

        Estimacion::create($request->all());

        return $this->respondStore();
    }

    public function show(Estimacion $estimacion)
    {
        return $estimacion;
    }

    public function update(Request $request, Estimacion $estimacion)
    {
        $this->validate($request, [
            'valor'            => 'sometimes|required|integer',
            'total'            => 'sometimes|required|integer',
            'punto_entrega_ig' => 'sometimes|required',
            'producto_id'      => 'sometimes|required',
        ]);

        $estimacion->fill($request->all())->save();

        return $this->respondUpdate();
    }

    public function destroy(Estimacion $estimacion)
    {
        $estimacion->delete();

        return $this->respondDestroy();
    }
}
