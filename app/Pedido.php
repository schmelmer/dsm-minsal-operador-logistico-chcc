<?php

namespace App;

use App\Filters\Filterable;
use App\RefreshesInventarioCache;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use Filterable, RefreshesInventarioCache;

    protected $fillable = [
        'punto_entrega_id',
        'detalle',
        'agno',
        'observacion',
        'estado',
        'historial',
        'guia',
        'evaluacion',
        'evaluacion_observacion',
        'observacion_distribuidor'
    ];

    protected $casts = [
        'detalle'   => 'json',
        'historial' => 'json'
    ];

    protected $appends = [
        'diff',
        'folio'
    ];

    public function puntoentrega()
    {
        return $this->belongsTo(PuntoEntrega::class, 'punto_entrega_id');
    }

    public function getFolioAttribute()
    {
        return $this->id + 501249;
    }

    public function getDiffAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function estadoFormateado()
    {
        return ucwords(str_replace('_', ' ', $this->estado));
    }

    public function fueEntregado()
    {
        return !is_null($this->guia);
    }
}
