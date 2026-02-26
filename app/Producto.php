<?php

namespace App;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use Filterable;

    protected $fillable = [
        'name',
        'categoria_id',
        'poblacion_id',
        'stock',
        'year',
        'cajas',
        'de_servicios',
        'de_hospitales',
        'estado',
        'excluye_hospitales_secundarios',
    ];

    protected $appends = [
        'stock_cajas'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function poblacion()
    {
        return $this->belongsTo(Poblacion::class, 'poblacion_id');
    }

    public function scopeAgnoActual($query)
    {
        $agnoActual = Configuracion::first()->agno;

        return $query->where('year', $agnoActual);
    }

    public function getStockCajasAttribute()
    {
        if ($this->cajas == 0) {
            return 0;
        }
        return round($this->stock / $this->cajas);
    }
}
