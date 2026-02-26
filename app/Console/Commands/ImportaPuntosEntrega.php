<?php

namespace App\Console\Commands;

use DB;
use App\User;
use App\Comuna;
use App\Region;
use App\ServicioSalud;
use App\Establecimiento;
use App\PuntoEntrega;
use Illuminate\Console\Command;

class ImportaPuntosEntrega extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chcc:importa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa puntos de entrega';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::transaction(function () {
            $file = fopen(dirname(__FILE__) . '/operador.csv', 'r');

            $this->readLine($file);
            while ($row = $this->readLine($file)) {
                $email = trim($row[10]);
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn('Correo no valido:' . $email);
                    continue;
                }
                $this->creaPuntoEntrega($this->creaResponsable($email, $row), $row);
            }
        });
    }

    private function creaResponsable($email, $row)
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->fill([
                'name' => $row[8],
                'cargo' => $row[9],
                'password' => $email,
                'telefono' => $row[12],
            ])->save();

        return $user->syncRoles(['solicitante']);
    }

    private function creaPuntoEntrega($user, $row)
    {
        $tipo = $row[0];
        $codigo = trim($row[4]);

        $servicio = ServicioSalud::whereName(trim($row[2]))->first();
        $region = $servicio->region_id;

        if ($tipo == 'Centro de Salud') {
            $establecimiento = Establecimiento::whereCode($codigo)->first();
            if ($establecimiento) {
                $comuna = $establecimiento->comuna;
                $servicio =  $comuna->servicio;
                $region = $servicio->region_id;
            }
            $tipoTerritorio = 'establecimiento';
        } elseif ($tipo == 'Servicio de Salud') {
            $tipoTerritorio = 'servicio';
            $region = $servicio->region_id;
            $comuna = Comuna::whereRaw("unaccent(name) = unaccent(?)", trim($row[3]))->first();
        } elseif ($tipo == 'Municipio') {
            $tipoTerritorio = 'comuna';
            $comuna = Comuna::whereRaw("unaccent(name) = unaccent(?)", trim($row[3]))->first();
        }

        // temporal
        if ($tipoTerritorio == 'establecimiento' && ! $establecimiento) {
            $this->warn('Establecimiento con codigo: ' . $codigo);
            return;
        } else if ($tipoTerritorio == 'region' && ! $region) {
            $this->warn('Region con codigo: ' . $codigo);
            return;
        } else if ($tipoTerritorio == 'servicio' && ! $servicio) {
            $this->warn('Servicio con codigo: ' . $codigo);
            return;
        } else if ($tipoTerritorio == 'comuna' && ! $comuna) {
            $this->warn('Comuna con codigo: ' . $codigo);
            return;
        }

        // periodicidad
        $periodo = strtolower(trim($row[13]));
        if ($periodo == "anual") {
            $periodicidad = 12;
        } else if ($periodo == "mensual") {
            $periodicidad = 1;
        } else if ($periodo == "bimensual") {
            $periodicidad = 2;
        } else if ($periodo == "trimestral") {
            $periodicidad = 3;
        } else if ($periodo == "cuatrimestral") {
            $periodicidad = 4;
        } else if ($periodo == "semestral") {
            $periodicidad = 6;
        } else if ($periodo == "bimensual") {
            $periodicidad = 2;
        } else {
            $this->warn('Periodo null');
            $this->warn(var_dump($row));
            $periodicidad = null;
        }


        PuntoEntrega::create([
            'territorio_type' => $tipoTerritorio,
            'calle' => $row[6],
            'numero' => $row[7],
            'periodicidad' => $periodicidad,
            'region_id' => $region,
            'servicio_id' => $servicio->id,
            'comuna_id' => $comuna->id,
            'establecimiento_id' => isset($establecimiento) ? $establecimiento->id : null,
            'user_id' => $user->id
        ]);
    }

    private function readLine($file)
    {
        return fgetcsv($file, 1024);
    }
}
