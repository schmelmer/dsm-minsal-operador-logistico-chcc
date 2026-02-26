<?php

namespace App\Filters;

class CategoriaFilter extends QueryFilter
{
    public function name($value = null)
    {
        return $this->builder->where('name', 'ilike', "%$value%");
    }
}
