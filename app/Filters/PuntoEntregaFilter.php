<?php

namespace App\Filters;

class PuntoEntregaFilter extends QueryFilter
{
    public function hospital($value)
    {
        if ($value) {
            return $this->builder->whereHas('establecimiento', function ($q) use ($value) {
                $q->whereNotNull('hospital');
            });
        }
    }

    public function tipo($value = null)
    {
        $values = explode(',', $value);
        if (count($values) > 1) {
            return $this->builder->whereIn('territorio_type', $values);
        }
        return $this->builder->where('territorio_type', $value);
    }

    public function region($value = null)
    {
        return $this->builder->whereHas('region', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function comuna($value = null)
    {
        return $this->builder->whereHas('comuna', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function servicio($value = null)
    {
        return $this->builder->whereHas('servicio', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function establecimiento($value = null)
    {
        return $this->builder->whereHas('establecimiento', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function responsable($value = null)
    {
        return $this->builder->whereHas('responsable', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function userId($value = null)
    {
        return $this->builder->where('user_id', $value);
    }
}
