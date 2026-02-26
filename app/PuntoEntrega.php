<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filters\Filterable;

class PuntoEntrega extends Model
{
    use Filterable;

    protected $table = 'punto_entregas';

    protected $fillable = [
        'calle',
        'numero',
        'region_id',
        'servicio_id',
        'comuna_id',
        'establecimiento_id',
        'user_id',
        'territorio_id',
        'territorio_type',
        'periodicidad'
    ];

    protected $perPage = 30;

    protected $appends = [
        'direccion',
        'tipo',
        'diff'
    ];

    public function territorio()
    {
        $t = isset($this->territorio_type) ? $this->territorio_type : '';
        return $this->morphTo(null, null, $t . "_id");
    }

    public function territorioServicio()
    {
        return $this->morphTo(null, 'territorio_type', "servicio_id");
    }

    public function territorioComuna()
    {
        return $this->morphTo(null, 'territorio_type', "comuna_id");
    }

    public function territorioEstablecimiento()
    {
        return $this->morphTo(null, 'territorio_type', "establecimiento_id");
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class);
    }

    public function servicio()
    {
        return $this->belongsTo(ServicioSalud::class);
    }

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function getPeriodicidadAttribute()
    {
        if (is_null($this->attributes['periodicidad'])) {
            return null;
        }
        return (string)$this->attributes['periodicidad'];
    }

    public function getDireccionAttribute()
    {
        return "{$this->attributes['calle']} {$this->attributes['numero']}";
    }

    public function getTipoAttribute()
    {
        if ($this->territorio_type == 'region') {
            return 'Region: ' . $this->region->name;
        } else if ($this->territorio_type == 'comuna') {
            return 'Comuna: ' . $this->comuna->name;
        } else if ($this->territorio_type == 'servicio') {
            return 'Servicio salud: ' . $this->servicio->name;
        }
        return 'Establecimiento: ' . $this->establecimiento->name;
    }

    public function getTerritorialidadAttribute()
    {
        if ($this->territorio_type == 'region') {
            return 'Region';
        } else if ($this->territorio_type == 'comuna') {
            return 'Municipio';
        } else if ($this->territorio_type == 'servicio') {
            return 'Servicio salud';
        }
        return 'Establecimiento';
    }

    public function getDiffAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
