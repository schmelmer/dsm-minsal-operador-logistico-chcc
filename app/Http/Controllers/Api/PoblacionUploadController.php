<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Poblacion;
use App\Establecimiento;

class PoblacionUploadController extends ApiController
{
    public function upload(Request $request, Poblacion $poblacion)
    {
        $rows = collect($request->data);

        $notfound = $rows->filter(function ($row) {
            return Establecimiento::whereCode($row['Codigo'])->get() == null;
        });

        $poblacion->data = $rows->map(function ($row) {
            $collection = collect($row);
            return [
                'Codigo' => $collection->get('Codigo'),
                'Valor' => $collection->get('Valor', $collection->get('Año')),
            ];
        });

        file_put_contents(database_path() . '/seeds/poblaciones/' . $poblacion->name . '.json', json_encode($poblacion->data));

        $poblacion->save();

        return $this->respondStore('Población cargada exitosamente');
    }
}
