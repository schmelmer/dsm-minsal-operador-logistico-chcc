<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filters\Filterable;

class Region extends Model
{
    use Filterable;

    protected $fillable = [
        'name',
        'code',
    ];

    public function servicios()
    {
        return $this->hasMany(ServicioSalud::class);
    }

    public function puntoentrega()
    {
        return $this->morphMany(PuntoEntrega::class, 'territorio', null, 'region_id');
    }
}
