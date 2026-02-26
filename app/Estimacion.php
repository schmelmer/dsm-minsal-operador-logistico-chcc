<?php

namespace App;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class Estimacion extends Model
{
    use Filterable;

    protected $table = 'estimaciones';

    protected $fillable = [
        'punto_entrega_id',
        'producto_id',
        'cajas_total',
        'poblacion_asignada',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function puntoentrega()
    {
        return $this->belongsTo(PuntoEntrega::class, 'punto_entrega_id');
    }
}
