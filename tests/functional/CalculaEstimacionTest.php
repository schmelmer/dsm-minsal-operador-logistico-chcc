<?php

use App\Establecimiento;
use App\Poblacion;
use App\Producto;
use App\PuntoEntrega;
use App\Region;
use App\Services\CalculaEstimacion;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CalculaEstimacionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function obtiene_la_poblacion_de_establecimientos()
    {
        $establecimientoPuntoEntrega = factory(Establecimiento::class)->create(['code' => 123]);
        $establecimientoNoEsPuntoEntrega = factory(Establecimiento::class)->create(['code' => 456]);

        factory(PuntoEntrega::class)->states('establecimiento')->create([
            'region_id' => $establecimientoPuntoEntrega->comuna->servicio->region->id,
            'servicio_id' => $establecimientoPuntoEntrega->comuna->servicio->id,
            'comuna_id' =>  $establecimientoPuntoEntrega->comuna->id,
            'establecimiento_id' => $establecimientoPuntoEntrega->id,
        ]);

        $poblacion = factory(Poblacion::class)->create([
            'year' => 2016,
            'data' => [['Codigo' => 123, 'Valor' => 20], ['Codigo' => 456, 'Valor' => 30]]
        ]);

        $producto = factory(Producto::class)->create([
            'poblacion_id' => $poblacion->id,
            'stock'        => 170000,
            'cajas'        => 30,
            'estado'       => true
        ]);

        $estimaciones = new CalculaEstimacion;
        $estimaciones->calcula($producto);

        dd($estimaciones->poblacionesTerritoriales()['establecimientos']);
        $this->assertTrue($estimaciones->poblacionesTerritoriales()['establecimientos']->contains(['Codigo' => 123, 'Valor' => 20]));
        $this->assertFalse($estimaciones->poblacionesTerritoriales()['establecimientos']->contains(['Codigo' => 456, 'Valor' => 30]));
    }

    /**
     * @test
     */
    public function obtiene_la_poblacion_de_municipios()
    {
        $establecimientoPuntoEntrega = factory(Establecimiento::class)->create(['code' => 123]);
        $establecimientoNoEsPuntoEntregaA = factory(Establecimiento::class)->create(['code' => 456]);
        $establecimientoNoEsPuntoEntregaB = factory(Establecimiento::class)->create(['code' => 789, 'comuna_id' => $establecimientoNoEsPuntoEntregaA->comuna_id]);
        factory(PuntoEntrega::class)->states('establecimiento')->create([
            'region_id' => $establecimientoPuntoEntrega->comuna->servicio->region->id,
            'servicio_id' => $establecimientoPuntoEntrega->comuna->servicio->id,
            'comuna_id' =>  $establecimientoPuntoEntrega->comuna->id,
            'establecimiento_id' => $establecimientoPuntoEntrega->id,
        ]);
        factory(PuntoEntrega::class)->states('municipio')->create([
            'region_id' => $establecimientoNoEsPuntoEntregaA->comuna->servicio->region->id,
            'servicio_id' => $establecimientoNoEsPuntoEntregaA->comuna->servicio->id,
            'comuna_id' =>  $establecimientoNoEsPuntoEntregaA->comuna->id,
            'establecimiento_id' => $establecimientoNoEsPuntoEntregaA->id,
        ]);
        factory(Poblacion::class)->create([
            'year' => 2016,
            'data' => [['Codigo' => 123, 'Valor' => 20], ['Codigo' => 456, 'Valor' => 30], ['Codigo' => 789, 'Valor' => 4]]
        ]);

        $estimaciones = new CalculaEstimacion;
        $estimaciones->calcula();

        $this->assertTrue($estimaciones->poblacionesTerritoriales()['municipios']->contains(['Codigo' => $establecimientoNoEsPuntoEntregaA->comuna->code, 'Valor' => 34]));
        $this->assertEmpty($estimaciones->poblacionesTerritoriales()['municipios']->where('Codigo', 123));
    }

    /**
     * @test
     */
    public function obtiene_la_poblacion_de_servicios()
    {
        $establecimientoPuntoEntrega = factory(Establecimiento::class)->create(['code' => 123]);
        $establecimientoNoEsPuntoEntregaA = factory(Establecimiento::class)->create(['code' => 456]);
        $establecimientoNoEsPuntoEntregaB = factory(Establecimiento::class)->create(['code' => 789]);
        factory(PuntoEntrega::class)->states('establecimiento')->create([
            'region_id' => $establecimientoPuntoEntrega->comuna->servicio->region->id,
            'servicio_id' => $establecimientoPuntoEntrega->comuna->servicio->id,
            'comuna_id' =>  $establecimientoPuntoEntrega->comuna->id,
            'establecimiento_id' => $establecimientoPuntoEntrega->id,
        ]);
        factory(PuntoEntrega::class)->states('municipio')->create([
            'region_id' => $establecimientoNoEsPuntoEntregaA->comuna->servicio->region->id,
            'servicio_id' => $establecimientoNoEsPuntoEntregaA->comuna->servicio->id,
            'comuna_id' =>  $establecimientoNoEsPuntoEntregaA->comuna->id,
            'establecimiento_id' => $establecimientoNoEsPuntoEntregaA->id,
        ]);
        factory(PuntoEntrega::class)->states('servicio')->create([
            'region_id' => $establecimientoNoEsPuntoEntregaB->comuna->servicio->region->id,
            'servicio_id' => $establecimientoNoEsPuntoEntregaB->comuna->servicio->id,
            'comuna_id' =>  $establecimientoNoEsPuntoEntregaB->comuna->id,
            'establecimiento_id' => $establecimientoNoEsPuntoEntregaB->id,
        ]);
        factory(Poblacion::class)->create([
            'year' => 2016,
            'data' => [['Codigo' => 123, 'Valor' => 20], ['Codigo' => 456, 'Valor' => 30], ['Codigo' => 789, 'Valor' => 4]]
        ]);

        $estimaciones = new CalculaEstimacion;
        $estimaciones->calcula();

        $this->assertTrue($estimaciones->poblacionesTerritoriales()['servicios']->contains(['Codigo' => $establecimientoNoEsPuntoEntregaB->comuna->servicio->code, 'Valor' => 4]));
    }

    /**
     * @test
     */
    public function obtiene_la_poblacion_de_regiones()
    {
        $establecimientoPuntoEntrega = factory(Establecimiento::class)->create(['code' => 123]);
        $establecimientoNoEsPuntoEntregaA = factory(Establecimiento::class)->create(['code' => 456]);
        $establecimientoNoEsPuntoEntregaB = factory(Establecimiento::class)->create(['code' => 789]);
        factory(PuntoEntrega::class)->states('establecimiento')->create([
            'region_id' => $establecimientoPuntoEntrega->comuna->servicio->region->id,
            'servicio_id' => $establecimientoPuntoEntrega->comuna->servicio->id,
            'comuna_id' =>  $establecimientoPuntoEntrega->comuna->id,
            'establecimiento_id' => $establecimientoPuntoEntrega->id,
        ]);
        factory(PuntoEntrega::class)->states('municipio')->create([
            'region_id' => $establecimientoNoEsPuntoEntregaA->comuna->servicio->region->id,
            'servicio_id' => $establecimientoNoEsPuntoEntregaA->comuna->servicio->id,
            'comuna_id' =>  $establecimientoNoEsPuntoEntregaA->comuna->id,
            'establecimiento_id' => $establecimientoNoEsPuntoEntregaA->id,
        ]);
        factory(PuntoEntrega::class)->states('region')->create([
            'region_id' => $establecimientoNoEsPuntoEntregaB->comuna->servicio->region->id,
            'servicio_id' => $establecimientoNoEsPuntoEntregaB->comuna->servicio->id,
            'comuna_id' =>  $establecimientoNoEsPuntoEntregaB->comuna->id,
            'establecimiento_id' => $establecimientoNoEsPuntoEntregaB->id,
        ]);
        factory(Poblacion::class)->create([
            'year' => 2016,
            'data' => [['Codigo' => 123, 'Valor' => 20], ['Codigo' => 456, 'Valor' => 30], ['Codigo' => 789, 'Valor' => 4]]
        ]);

        $estimaciones = new CalculaEstimacion;
        $estimaciones->calcula();

        $this->assertTrue($estimaciones->poblacionesTerritoriales()['regiones']->contains(['Codigo' => $establecimientoNoEsPuntoEntregaB->comuna->servicio->region->code, 'Valor' => 4]));
    }
}
