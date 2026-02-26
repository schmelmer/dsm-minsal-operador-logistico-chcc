<?php

use Illuminate\Database\Seeder;
use App\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdmin = User::firstOrCreate([
            'name' => 'super admin',
            'email' => 'super@chcc.cl',
            'password' => '123',
            'cargo' => 'Super Administrar',
            'telefono' => '123123123'
        ]);

        if (! $superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }

        $admin = User::firstOrCreate([
            'name' => 'admin',
            'email' => 'admin@chcc.cl',
            'password' => '123',
            'cargo' => 'Administrar',
            'telefono' => '22222222'
        ]);

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $admin = User::firstOrCreate([
            'name' => 'TNT',
            'email' => 'carolina.madariaga@tntlit.cl',
            'password' => 'carolina.madariaga@tntlit.cl',
            'cargo' => 'Ejecutivo Grandes Cuentas',
            'telefono' => ''
        ]);

        if (! $admin->hasRole('distribuidor')) {
            $admin->assignRole('distribuidor');
        }
    }
}
