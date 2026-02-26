<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\Producto;
use Illuminate\Http\Request;
use App\Filters\ProductoFilter;
use App\Services\CalculaEstimacion;
use App\Http\Controllers\ApiController;

class ProductoController extends ApiController
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index(ProductoFilter $filter)
    {
        $query = Producto::with('categoria:id,name', 'poblacion:id,name')->filter($filter)->orderBy('name');

        return $this->respondIndex($query);
    }

    public function store(Request $request, CalculaEstimacion $estimacion)
    {
        $v = Validator::make(request()->all(), [
            'name' => 'required|max:255',
            'stock' => 'required|integer',
            'cajas' => 'required|integer',
            'year' => 'required|integer',
            'categoria_id' => 'required',
        ]);

        $v->sometimes('poblacion_id', 'required', function ($input) {
            return !$input->de_servicios && !$input->de_hospitales;
        });
        $v->validate();


        if ($request->de_servicios || $request->de_hospitales) {
            $request->merge(['poblacion_id' => null]);
        }

        $p = Producto::create($request->all());

        if (!is_null($p->poblacion_id)) {
            $estimacion->calcula($p);
        }

        return $this->respondStore();
    }

    public function show(Producto $producto)
    {
        return $producto;
    }

    public function update(Request $request, Producto $producto, CalculaEstimacion $estimacion)
    {
        $v = Validator::make(request()->all(), [
            'name' => 'sometimes|required|max:255',
            'stock' => 'sometimes|required|integer',
            'cajas' => 'sometimes|required|integer',
            'year' => 'sometimes|required|integer',
            'categoria_id' => 'sometimes|required',
        ]);

        $v->sometimes('poblacion_id', 'required', function ($input) {
            return !$input->de_servicios && !$input->de_hospitales;
        });

        $v->validate();

        if ($request->de_servicios || $request->de_hospitales) {
            $request->merge(['poblacion_id' => null]);
        }

        // Laravel no maneja bien el isDirty con booleanos al parecer ...[]
        $excluyeHospitalSecundarioOld = $producto->excluye_hospitales_secundarios;

        $producto->fill($request->all());

        $isDirty = $producto->isDirty('stock')
            || $producto->isDirty('cajas')
            || $producto->isDirty('excluye_hospitales_secundarios')
            || $producto->isDirty('poblacion_id');

        $producto->save();

        if ($isDirty && !is_null($producto->poblacion_id)) {
            $estimacion->calcula($producto);
        }

        return $this->respondUpdate();
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return $this->respondDestroy();
    }
}
