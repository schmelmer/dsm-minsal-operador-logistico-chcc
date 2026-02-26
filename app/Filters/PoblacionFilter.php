<?php

namespace App\Filters;

use DB;

class PoblacionFilter extends QueryFilter
{
    public function name($value = null)
    {
        return $this->builder->where('name', 'ilike', "%$value%");
    }

    public function year($value = null)
    {
        return $this->builder->where(\DB::RAW("CAST(year AS TEXT)"), 'ilike', "%$value%");
    }
}
