<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filters\Filterable;

class Poblacion extends Model
{
    use Filterable;

    protected $table = 'poblaciones';

    protected $fillable = [
        'name',
        'year',
        'data'
    ];

    protected $casts = [
        'data' => 'json'
    ];

    protected $appends = [
        'dataCount',
        'poblacionCount',
        'name_with_year'
    ];

    protected $hidden = [
        'data'
    ];

    public function getDataCountAttribute()
    {
        return count((array)$this->data);
    }

    public function getPoblacionCountAttribute()
    {
        return collect($this->data)->sum('Valor');
    }

    public function getNameWithYearAttribute()
    {
        return $this->name . "($this->year)";
    }
}
