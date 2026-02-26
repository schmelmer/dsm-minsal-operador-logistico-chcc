<?php

namespace App\Filters;

class EstimacionFilter extends QueryFilter
{
    public function producto($value = null)
    {
        return $this->builder->whereHas('producto', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function productoId($value = null)
    {
        return $this->builder->whereProductoId($value);
    }

    public function puntoEntregaId($value = null)
    {
        return $this->builder->where('punto_entrega_id', $value);
    }

    public function tipo($value = null)
    {
        return $this->builder->whereHas('puntoentrega', function ($q) use ($value) {
            $q->where('territorio_type', $value);
        });
    }
}
