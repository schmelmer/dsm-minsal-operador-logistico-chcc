<?php

use App\Establecimiento;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class EstablecimientoTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function obtiene_los_codigos_a_partir_de_los_id()
    {
        $establecimientoA = factory(Establecimiento::class)->create(['code' => 123]);
        $establecimientoB = factory(Establecimiento::class)->create(['code' => 456]);
        $establecimientoC = factory(Establecimiento::class)->create(['code' => 789]);

        $establecimientos = Establecimiento::getCodesById([1, 2, 3]);

        $this->assertContains($establecimientoA->code, $establecimientos);
        $this->assertContains($establecimientoB->code, $establecimientos);
        $this->assertNotContains(000, $establecimientos);
        $this->assertCount(3, $establecimientos);
    }
}
