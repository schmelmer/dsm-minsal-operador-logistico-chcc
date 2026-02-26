<?php

namespace App\Http\Controllers\Api;

use App\Categoria;
use Illuminate\Http\Request;
use App\Filters\CategoriaFilter;
use App\Http\Controllers\ApiController;

class CategoriaController extends ApiController
{
    public function index(CategoriaFilter $filter)
    {
        $query = Categoria::filter($filter);

        return $this->respondIndex($query);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|unique:categorias',
        ]);

        Categoria::create($request->all());

        return $this->respondStore();
    }

    public function show(Categoria $categoria)
    {
        return $categoria;
    }

    public function update(Request $request, Categoria $categoria)
    {
        $this->validate($request, [
            'name' => 'required|max:255|unique:categorias,name,' . $categoria->id
        ]);

        $categoria->fill($request->all())->save();

        return $this->respondUpdate();
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return $this->respondDestroy();
    }
}
