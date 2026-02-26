<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimacionManual extends Model
{
    protected $table = 'estimaciones_manuales';

    protected $fillable = [
        'punto_entrega_id',
        'producto_id',
        'cajas_manuales',
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
