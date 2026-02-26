<?php

namespace App;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use Filterable;

    protected $fillable = [
        'name',
    ];
}
