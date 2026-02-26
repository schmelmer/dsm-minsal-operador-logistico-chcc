<?php

namespace App\Filters;

use App\Comuna;
use App\ServicioSalud;
use App\Establecimiento;

class PedidoFilter extends QueryFilter
{
    public function id($value = null)
    {
        return $this->builder->where('id', 'ilike', "%$value%");
    }

    public function agno($value = null)
    {
        return $this->builder->where('agno', $value);
    }

    public function puntoentrega($value = null)
    {
        return $this->builder->where('punto_entrega_id', $value);
    }

    public function punto($value = null)
    {
        return $this->builder->whereHas('puntoentrega', function ($query) use ($value) {
            $query->whereHasMorph('territorioServicio', [ServicioSalud::class], function ($q) use ($value) {
                $q->where('name', 'ilike', "%$value%");
            });

            $query->orWhereHasMorph('territorioComuna', [Comuna::class], function ($q) use ($value) {
                $q->where('name', 'ilike', "%$value%");
            });

            $query->orWhereHasMorph('territorioEstablecimiento', [Establecimiento::class], function ($q) use ($value) {
                $q->where('name', 'ilike', "%$value%");
            });
        });
    }

    public function puntosId($value = [])
    {
        $ids = explode(',', $value);

        return $this->builder->whereIn('punto_entrega_id', $ids);
    }

    public function servicio($value = null)
    {
        return $this->builder->whereHas('puntoentrega.servicio', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function comuna($value = null)
    {
        return $this->builder->whereHas('puntoentrega.comuna', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function establecimiento($value = null)
    {
        return $this->builder->whereHas('puntoentrega.establecimiento', function ($q) use ($value) {
            $q->where('name', 'ilike', "%$value%");
        });
    }

    public function estado($value = null)
    {
        return $this->builder->where('estado', $value);
    }

    public function evaluacion($value = null)
    {
        return $this->builder->where('evaluacion', $value);
    }
}
