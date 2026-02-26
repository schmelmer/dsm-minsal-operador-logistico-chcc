<?php

namespace App\Exports;

use App\Producto;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct()
    {
        $this->data = $this->getProductos();
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return collect($this->data->first())
            ->keys()
            ->toArray();
    }

    private function getProductos()
    {
        return Producto::with(
            'categoria:id,name',
            'poblacion:id,name'
        )->orderBy('name')
            ->get()
            ->map(function ($producto) {
                return [
                    'Nombre'                               => $producto->name,
                    'Stock'                                => $producto->stock,
                    'Unidades por caja'                    => $producto->cajas,
                    'Categoria'                            => $producto->categoria->name,
                    'Poblacion'                            => optional($producto->poblacion)->name,
                    'Año'                                  => $producto->year,
                    'Aplica solo a servicio y seremi'      => $producto->de_servicios ? 'si' : 'no',
                    'Aplica solo a hospitales'             => $producto->de_hospitales ? 'si' : 'no',
                    'Excluye hospitales nivel secundario' => $producto->excluye_hospitales_secundarios ? 'si' : 'no',
                    'Activo'                               => $producto->estado ? 'si' : 'no'
                ];
            });
    }
}
