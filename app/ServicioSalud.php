<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filters\Filterable;

class ServicioSalud extends Model
{
    use Filterable;

    protected $table = 'servicios';

    protected $fillable = [
        'name',
        'code',
        'region_id',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function comunas()
    {
        return $this->hasMany(Comuna::class, 'servicio_id');
    }

    public function puntoentrega()
    {
        return $this->morphMany(PuntoEntrega::class, 'territorio', null, 'servicio_id');
    }
}
