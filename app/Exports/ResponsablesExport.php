<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ResponsablesExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return User::select(
            'users.*',
            'comunas.name as comuna',
            'servicios.name as servicio',
            'establecimientos.name as establecimiento'
        )
            ->with('roles:id,label')
            ->leftJoin('punto_entregas', 'punto_entregas.user_id', 'users.id')
            ->leftJoin('servicios', 'punto_entregas.servicio_id', 'servicios.id')
            ->leftJoin('comunas', 'punto_entregas.comuna_id', 'comunas.id')
            ->leftJoin('establecimientos', 'punto_entregas.establecimiento_id', 'establecimientos.id')
            ->get()
            ->map(function ($responsable) {
                return [
                    'Nombre'           => $responsable->name,
                    'Email'            => $responsable->email,
                    'Cargo'            => $responsable->cargo,
                    'Telefono'         => $responsable->telefono,
                    'Roles'            => $responsable->roles->implode('label', ', '),
                    'Servicio'         => $responsable->servicio,
                    'Comuna'           => $responsable->comuna,
                    'Establecimientoo' => $responsable->establecimiento
                ];
            });
    }
}
