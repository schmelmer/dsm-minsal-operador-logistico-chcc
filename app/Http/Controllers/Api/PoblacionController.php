<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Poblacion;
use App\Filters\PoblacionFilter;
use App\Http\Controllers\ApiController;

class PoblacionController extends ApiController
{

    public function index(PoblacionFilter $filter)
    {
        $query = Poblacion::filter($filter);

        return $this->respondIndex($query);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'year' => 'required|integer',
        ]);

        $poblacion = Poblacion::create($request->all());

        return $this->respond([
            'message' => 'Creación exitosa. Suba archivo de población',
            'id' => $poblacion->id
        ]);
    }

    public function show(Poblacion $poblacion)
    {
        return $poblacion;
    }

    public function update(Request $request, Poblacion $poblacion)
    {
        $this->validate($request, [
            'name' => 'sometimes|required|max:255',
            'year' => 'sometimes|required|integer'
        ]);

        $poblacion->fill($request->all())->save();

        // actualizar estimaciones de los productos asociados a tal poblacion
        // if cambio data
        // $productos = Productos::wherePoblacionId($poblacion->id)->get()
        // $estimacion->calculaProductos($productos);

        return $this->respondUpdate();
    }

    public function destroy(Poblacion $poblacion)
    {
        $poblacion->delete();

        return $this->respondDestroy();
    }
}
