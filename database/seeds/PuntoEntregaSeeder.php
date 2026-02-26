<?php

use App\PuntoEntrega;
use App\Region;
use Illuminate\Database\Seeder;

class PuntoEntregaSeeder extends Seeder
{
    public function run()
    {
        foreach (range(0, 10) as $i) {
            $region = Region::whereHas('servicios.comunas.establecimientos')->inRandomOrder()->first();
            $servicio = $region->servicios()->whereHas('comunas.establecimientos')->inRandomOrder()->first();
            $comuna = $servicio->comunas()->whereHas('establecimientos')->inRandomOrder()->first();
            $establecimiento = $comuna->establecimientos()->inRandomOrder()->first();

            factory(PuntoEntrega::class)->states(collect(['region', 'establecimiento', 'municipio', 'servicio'])->random())->create([
                'region_id' => $region->id,
                'servicio_id' => $servicio->id,
                'comuna_id' => $comuna->id,
                'establecimiento_id' => $establecimiento->id,
            ]);
        }
    }
}
