<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\EstimacionManual;
use App\Http\Controllers\ApiController;

class EstimacionManualController extends ApiController
{
    public function index(EstimacionFilter $filter)
    {
        $query = EstimacionManual::get();

        return $this->respondIndex($query);
    }

    public function indexDeServicios()
    {
        return EstimacionManual::whereProductoId(request()->productoId)->get();
    }

    public function indexDeHospitales()
    {
        return EstimacionManual::whereProductoId(request()->productoId)->get();
    }

    public function store(Request $request)
    {
        foreach ($request->all() as $estimacion) {
            EstimacionManual::updateOrCreate(
                collect($estimacion)->only(['punto_entrega_id', 'producto_id'])->all(),
                ['cajas_manuales' => $estimacion['cajas_manuales']]
            );
        }

        return $this->respondStore();
    }

    public function show(Estimacion $estimacion)
    {
        return $estimacion;
    }

    public function update(Request $request, EstimacionManual $estimacion)
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
}
