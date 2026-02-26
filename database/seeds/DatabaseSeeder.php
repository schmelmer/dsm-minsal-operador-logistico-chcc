<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionsAndRoleSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(ConfiguracionSeeder::class);
        $this->call(RegionSeeder::class);
        $this->call(ServiciosSeeder::class);
        $this->call(ComunasSeeder::class);
        $this->call(EstablecimientoSeeder::class);
        $this->call(CategoriaSeeder::class);
        $this->call(PoblacionSeeder::class);
        $this->call(ProductoSeeder::class);
    }
}
