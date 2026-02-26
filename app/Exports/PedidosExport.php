<?php

namespace App\Exports;

use App\Pedido;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PedidosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct()
    {
        $this->data = $this->getPedidos();
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

    private function getPedidos()
    {
        return Pedido::whereHas('puntoentrega')
            ->with(
                'puntoentrega.responsable',
                'puntoentrega.comuna:id,name',
                'puntoentrega.servicio:id,name',
                'puntoentrega.establecimiento:id,name'
            )->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pedido) {
                return [
                    'ID'                => $pedido->id,
                    'Punto de entrega'  => $pedido->puntoentrega->tipo,
                    'Servicio de salud' => $pedido->puntoentrega->servicio->name,
                    'Comuna'            => $pedido->puntoentrega->comuna->name,
                    'Establecimiento'   => isset($pedido->puntoentrega->establecimiento) ? $pedido->puntoentrega->establecimiento->name : '',
                    'Fecha pedido'      => $pedido->created_at->format('d/m/Y'),
                    'Estado'            => str_replace('_', ' ', $pedido->estado),
                    'Evaluación'        => $pedido->evaluacion,
                ];
            });
    }
}
