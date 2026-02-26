<?php

namespace App\Providers;

use App\Region;
use App\Comuna;
use Carbon\Carbon;
use App\ServicioSalud;
use App\Establecimiento;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(LC_TIME, 'es_ES.utf8');
        Carbon::setLocale('es');

        Relation::morphMap([
            'region' => Region::class,
            'comuna' => Comuna::class,
            'servicio' => ServicioSalud::class,
            'establecimiento' => Establecimiento::class,
        ]);

        Validator::extendImplicit('required_poblacion', function ($attribute, $value, $parameters, $validator) {
            return request()->de_servicios || request()->de_hospitales;
        });

        Builder::macro('addSubSelect', function ($column, $query) {
            if (is_null($this->columns)) {
                $this->select($this->from . '.*');
            }

            return $this->selectSub($query->limit(1), $column);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
