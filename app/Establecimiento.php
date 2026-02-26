<?php

namespace App;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    use Filterable;

    protected $fillable = [
        'name',
        'code',
        'comuna_id',
        'hospital',
        'es_nivel_secundario',
    ];

    public function comuna()
    {
        return $this->belongsTo(Comuna::class);
    }

    public function puntoentrega()
    {
        return $this->morphMany(PuntoEntrega::class, 'territorio', null, 'establecimiento_id');
    }

    public function scopeGetCodesById($query, $establecimientosId = [])
    {
        return $query->select('code')->whereIn('id', $establecimientosId)->get()->pluck('code');
    }
}
