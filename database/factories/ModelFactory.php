<?php

use App\Categoria;
use App\Comuna;
use App\Establecimiento;
use App\Poblacion;
use App\Producto;
use App\PuntoEntrega;
use App\Region;
use App\ServicioSalud;
use App\User;

$factory->define(User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => $password ?: $password = 'secret',
        'remember_token' => str_random(10),
        'api_token' => str_random(60),
        'cargo' => $faker->jobTitle,
        'telefono' => $faker->numberBetween(56900000000, 56999999999)
    ];
});

$factory->define(Spatie\Permission\Models\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => str_slug($faker->name),
        'label' => $faker->sentence,
    ];
});

$factory->define(Spatie\Permission\Models\Permission::class, function (Faker\Generator $faker) {
    return [
        'name' => str_slug($faker->name),
        'label' => $faker->sentence,
    ];
});

$factory->define(Region::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->state,
        'code' => $faker->unique()->randomDigitNotNull
    ];
});

$factory->define(ServicioSalud::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->state,
        'code' => $faker->unique()->numberBetween(400, 700),
        'region_id' => function () {
            return factory(Region::class)->create()->id;
        }
    ];
});

$factory->define(Comuna::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->state,
        'code' => $faker->unique()->numberBetween(10000, 16000),
        'servicio_id' => function () {
            return factory(ServicioSalud::class)->create()->id;
        }
    ];
});

$factory->define(Establecimiento::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->state,
        'code' => $faker->unique()->numberBetween(20000, 36000),
        'comuna_id' => function () {
            return factory(Comuna::class)->create()->id;
        }
    ];
});

$factory->define(PuntoEntrega::class, function (Faker\Generator $faker) {
    return [
        'calle' => $faker->streetAddress,
        'numero' => $faker->randomDigit,
        'periodicidad' => $faker->numberBetween(1, 12),
        'region_id' => function () {
            return factory(Region::class)->create()->id;
        },
        'servicio_id' => function (array $puntoEntrega) {
            return factory(ServicioSalud::class)->create(['region_id' => $puntoEntrega['region_id']])->id;
        },
        'comuna_id' => function (array $puntoEntrega) {
            return factory(Comuna::class)->create(['servicio_id' => $puntoEntrega['servicio_id']])->id;
        },
        'establecimiento_id' => function (array $puntoEntrega) {
            return factory(Establecimiento::class)->create(['comuna_id' => $puntoEntrega['comuna_id']])->id;
        },
        'user_id' => function () {
            return factory(User::class)->create()->id;
        }
    ];
});

$factory->state(PuntoEntrega::class, 'establecimiento', function ($faker) {
    return [
        'territorio_type' => 'establecimiento'
    ];
});

$factory->state(PuntoEntrega::class, 'municipio', function ($faker) {
    return [
        'territorio_type' => 'comuna'
    ];
});

$factory->state(PuntoEntrega::class, 'servicio', function ($faker) {
    return [
        'territorio_type' => 'servicio'
    ];
});

$factory->state(PuntoEntrega::class, 'region', function ($faker) {
    return [
        'territorio_type' => 'region'
    ];
});

$factory->define(Poblacion::class, function (Faker\Generator $faker) {
    $year = $faker->numberBetween(2000, 2020);
    return [
        'name' => 'Poblacion ' . $year,
        'year' => $year,
        'data' => []
    ];
});

$factory->define(Categoria::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->randomElement(['Gestantes', 'Niños(as)', 'Padres', 'Profesionales'])
    ];
});

$factory->define(Producto::class, function (Faker\Generator $faker) {
    return [
        'name'                           => $faker->name,
        'categoria_id'                   => function () {
            return factory(Categoria::class)->create()->id;
        },
        'poblacion_id'                   => function () {
            return factory(Poblacion::class)->create()->id;
        },
        'stock'                          => $faker->numberBetween(35000, 200000),
        'year'                           => $faker->numberBetween(2000, 2020),
        'cajas'                          => $faker->numberBetween(30, 200),
        'de_servicios'                   => $faker->boolean,
        'de_hospitales'                  => $faker->boolean,
        'excluye_hospitales_secundarios' => $faker->boolean,
        'estado'                         => $faker->boolean,
    ];
});
