<?php

namespace App\Filters;

use DB;

class ProductoFilter extends QueryFilter
{
    public function estado($estado = 0)
    {
        return $this->builder->where('estado', (boolean)$estado);
    }

    public function seremi($value = 0)
    {
        return $this->builder->where('de_servicios', (boolean)$value);
    }

    public function hospitales($value = 0)
    {
        return $this->builder->where('de_hospitales', (boolean)$value);
    }

    public function name($value = null)
    {
        return $this->builder->where('name', 'ilike', "%$value%");
    }

    public function poblacion($value = null)
    {
        return $this->builder->whereHas('poblacion', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function categoria($value = null)
    {
        return $this->builder->whereHas('categoria', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function stock($value = null)
    {
        return $this->builder->where(DB::RAW("CAST(stock AS TEXT)"), 'ilike', "%$value%");
    }

    public function cajas($value = null)
    {
        return $this->builder->where(DB::RAW("CAST(cajas AS TEXT)"), 'ilike', "%$value%");
    }

    public function year($value = null)
    {
        return $this->builder->where(DB::RAW("CAST(year AS TEXT)"), 'ilike', "%$value%");
    }
}
