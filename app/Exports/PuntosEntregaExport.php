<?php

namespace App\Exports;

use App\PuntoEntrega;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PuntosEntregaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct()
    {
        $this->data = $this->getPuntosEntrega();
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

    private function getPuntosEntrega()
    {
        return PuntoEntrega::with(
            'region:id,name',
            'servicio:id,name',
            'comuna:id,name',
            'establecimiento:id,name',
            'responsable'
        )->get()->each(function ($punto) {
            $punto->append('territorialidad');
        })->map(function ($punto) {
            return [
                'Tipo'              => $punto->territorialidad,
                'Region'            => $punto->region->name,
                'Servicio de salud' => $punto->servicio->name,
                'Comuna'            => $punto->comuna->name,
                'Establecimiento'   => isset($punto->establecimiento) ? $punto->establecimiento->name : '',
                'Direccion'         => $punto->direccion,
                'Responsable'       => $punto->responsable->name,
                'Cargo'             => $punto->responsable->cargo,
                'Email'             => $punto->responsable->email,
                'Fono'              => $punto->responsable->telefono,
            ];
        });
    }
}
