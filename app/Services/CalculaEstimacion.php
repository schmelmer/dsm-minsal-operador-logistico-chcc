<?php

namespace App\Services;

use \DB;
use App\Comuna;
use App\Region;
use App\Producto;
use App\Estimacion;
use \Carbon\Carbon;
use App\ServicioSalud;

class CalculaEstimacion
{
    const CHUNK = 300;
    protected $puntosEntrega;
    protected $poblacion;
    protected $poblacionesTerritoriales = [];
    protected $estimaciones;
    protected $now;
    protected $establecimientos;
    protected $comunas;
    protected $servicios;

    public function __construct()
    {
        ini_set('max_execution_time', 180); //3 minutes
        $this->estimaciones = collect([]);
        $this->now = Carbon::now();

        // Establecimientos ...
        $this->establecimientos = DB::table('establecimientos')->select('id', 'code', 'comuna_id')->get();
        $this->comunas = Comuna::select('id', 'code', 'servicio_id')->with('establecimientos:comuna_id,code')->get();
        $this->servicios = ServicioSalud::select('id', 'code', 'region_id')->with('comunas.establecimientos')->get();
        $this->regiones = Region::select('id', 'code')->with('servicios.comunas.establecimientos')->get();

        // Establecimientos excluido para nivel secundario
        $this->establecimientosExcluidos = DB::table('establecimientos')->select('id', 'code', 'comuna_id')->where('es_nivel_secundario', false)->get();
        $this->comunasExcluidos = Comuna::select('id', 'code', 'servicio_id')->with(['establecimientos' => function ($query) {
            return $query->select('comuna_id', 'code')->where('es_nivel_secundario', false);
        }])->get();
        $this->serviciosExcluidos = ServicioSalud::select('id', 'code', 'region_id')->with(['comunas.establecimientos' => function ($query) {
            return $query->select('comuna_id', 'code')->where('es_nivel_secundario', false);
        }])->get();
        $this->regionesExcluidos = Region::select('id', 'code')->with(['servicios.comunas.establecimientos' => function ($query) {
            return $query->select('comuna_id', 'code')->where('es_nivel_secundario', false);
        }])->get();
    }

    public function calcula(Producto $p)
    {
        $this->producto = $p;
        $this->poblacion = collect($p->poblacion->data);
        $this->puntosEntrega = DB::table('punto_entregas')->get()->groupBy('territorio_type');
        $this->calculaPoblaciones();
        $this->calculaEstimaciones();
    }

    public function calculaProductos($productos = null)
    {
        $this->puntosEntrega = DB::table('punto_entregas')->get()->groupBy('territorio_type');
        $productos = isset($productos) ? $productos : Producto::with('poblacion')->whereDeServicios(false)->whereDeHospitales(false)->get();
        $productos->each(function ($p) {
            $this->producto = $p;
            $this->poblacion = collect($p->poblacion->data);
            $this->calculaPoblaciones();
            $this->calculaEstimaciones();
        });
    }

    private function calculaPoblaciones()
    {
        $this->establecimientos();
        $this->municipios();
        $this->servicios();
        $this->regiones();
    }

    private function calculaEstimaciones()
    {
        $this->remueveSiExiste();

        $territorios = collect($this->poblacionesTerritoriales)->flatten(1)->sortByDesc('Valor');

        // poblacion total y ratio
        $poblacionTotal = $this->poblacionesTerritoriales['establecimientos']->sum('Valor')
            + $this->poblacionesTerritoriales['municipios']->sum('Valor')
            + $this->poblacionesTerritoriales['servicios']->sum('Valor')
            + $this->poblacionesTerritoriales['regiones']->sum('Valor');

        $stockTotalCajas = round($this->producto->stock / $this->producto->cajas);
        $stockRepartir   = $stockTotalCajas - $territorios->count();
        $stockAsegurado  =  $territorios->count() > $stockTotalCajas ? $stockTotalCajas : $territorios->count();
        $ratio = $stockRepartir / $poblacionTotal;

        $cajasRepartidas = 0;
        $territorios->each(function ($territorio) use (&$cajasRepartidas, $ratio, $stockRepartir, &$stockAsegurado) {
            $cajas = $this->calculaCajas($territorio, $ratio);
            $cajasRepartidas += $cajas;

            if ($cajas < 0 || $cajasRepartidas > $stockRepartir) {
                $cajas = 0;
            }

            if ($stockAsegurado > 0) {
                $cajas += 1;
                $stockAsegurado--;
            }

            $this->createEstimacion($territorio, $cajas);
        });

        // inserta estimaciones restantes
        if ($this->estimaciones->count() > 0) {
            $this->insertaEstimaciones();
            $this->estimaciones = collect([]);
        }
    }

    private function remueveSiExiste()
    {
        Estimacion::whereProductoId($this->producto->id)->delete();
    }

    private function establecimientos()
    {
        // poblaciones por establecimientos
        $puntosEstablecimientos = $this->puntosEntrega->get('establecimiento', collect([]));
        $establecimientosId = $puntosEstablecimientos->pluck('establecimiento_id');
        $this->poblacionesTerritoriales['establecimientos']  = $this->getEstablecimientos()
            ->whereIn('id', $establecimientosId)
            ->map(function ($establecimiento) use ($puntosEstablecimientos) {
                return [
                    'id'           => $establecimiento->id,
                    'Codigo'       => $establecimiento->code,
                    'Valor'        => optional($this->poblacion->firstWhere('Codigo', $establecimiento->code))['Valor'] ?? 0,
                    'puntoEntrega' => $puntosEstablecimientos->first(function ($e) use ($establecimiento) {
                        return $e->establecimiento_id == $establecimiento->id;
                    })->id
                ];
            });
    }

    private function municipios()
    {
        $puntosMunicipios = $this->puntosEntrega->get('comuna', collect([]));
        $municipiosId = $puntosMunicipios->pluck('comuna_id');
        $this->poblacionesTerritoriales['municipios'] = $this->getComunas()
            ->whereIn('id', $municipiosId)
            ->map(function ($municipio) use ($puntosMunicipios) {
                // poblacion total de los municipios
                $establecimientosMunicipio = $municipio->establecimientos->pluck('code');
                $totalMunicipio = $this->sumaPoblacionEstablecimientos($establecimientosMunicipio);

                // poblacion establecimientos que son punto de entrega
                $establecimientosPuntosEntrega = $this->getEstablecimientos()->whereIn('id', $this->puntosEntrega->get('establecimiento', collect([]))->where('comuna_id', $municipio->id)->pluck('establecimiento_id'))->pluck('code');
                $totalEstablecimientosPuntosEntrega = $this->sumaPoblacionEstablecimientos($establecimientosPuntosEntrega);

                return [
                    'id'           => $municipio->id,
                    'Codigo'       => $municipio->code,
                    'Valor'        => $totalMunicipio - $totalEstablecimientosPuntosEntrega,
                    'puntoEntrega' => $puntosMunicipios->first(function ($p) use ($municipio) {
                        return $p->comuna_id == $municipio->id;
                    })->id
                ];
            });
    }

    private function servicios()
    {
        $puntosServicios = $this->puntosEntrega->get('servicio', collect([]));
        $serviciosId = $puntosServicios->pluck('servicio_id');
        $this->poblacionesTerritoriales['servicios'] = $this->getServicios()
            ->whereIn('id', $serviciosId)
            ->map(function ($servicio) use ($puntosServicios) {
                // poblacion total de los servicios
                $establecimientosId = $servicio->comunas->flatMap(function ($comuna) {
                    return $comuna->establecimientos->pluck('code');
                });
                $totalServicio = $this->sumaPoblacionEstablecimientos($establecimientosId);

                // poblacion municipios que son punto de entrega
                $municipiosPuntosEntrega = $this->getComunas()->whereIn('id', $this->puntosEntrega->get('comuna', collect([]))->where('servicio_id', $servicio->id)->pluck('comuna_id'))->pluck('code');
                $totalMunicipiosPuntosEntrega = $municipiosPuntosEntrega->map(function ($m) {
                    return $this->poblacionesTerritoriales['municipios']->where('Codigo', $m)->first();
                })->sum('Valor');

                // poblacion establecimientos que son punto de entrega
                $establecimientosPuntosEntrega = $this->getEstablecimientos()->whereIn('id', $this->puntosEntrega->get('establecimiento', collect([]))->where('servicio_id', $servicio->id)->pluck('establecimiento_id'))->pluck('code');
                $totalEstablecimientosPuntosEntrega = $this->sumaPoblacionEstablecimientos($establecimientosPuntosEntrega);

                return [
                    'id'  => $servicio->id,
                    'Codigo'  => $servicio->code,
                    'Valor' => $totalServicio - $totalMunicipiosPuntosEntrega - $totalEstablecimientosPuntosEntrega,
                    'puntoEntrega' => $puntosServicios->first(function ($p) use ($servicio) {
                        return $p->servicio_id == $servicio->id;
                    })->id
                ];
            });
    }

    private function regiones()
    {
        $puntosRegiones = $this->puntosEntrega->get('region', collect([]));
        $regionesId = $puntosRegiones->pluck('region_id');
        $this->poblacionesTerritoriales['regiones'] = $this->getRegiones()
            ->whereIn('id', $regionesId)
            ->map(function ($region) use ($puntosRegiones) {
                // poblacion total de los regiones
                $establecimientosId = $region->servicios->flatMap(function ($servicio) {
                    return $servicio->comunas->flatMap(function ($comuna) {
                        return $comuna->establecimientos->pluck('code');
                    });
                });
                $totalRegion = $this->sumaPoblacionEstablecimientos($establecimientosId);

                // poblacion servicios que son punto de entrega
                $serviciosPuntosEntrega = $this->getServicios()->whereIn('id', $this->puntosEntrega->get('servicio', collect([]))->where('region_id', $region->id)->pluck('servicio_id'))->pluck('code');
                $totalServiciosPuntosEntrega = $serviciosPuntosEntrega->map(function ($s) {
                    return $this->poblacionesTerritoriales['servicios']->where('Codigo', $s)->first();
                })->sum('Valor');

                // poblacion municipios que son punto de entrega
                $municipiosPuntosEntrega = $this->getComunas()->whereIn('id', $this->puntosEntrega->get('comuna', collect([]))->where('region_id', $region->id)->pluck('comuna_id'))->pluck('code');
                $totalMunicipiosPuntosEntrega = $municipiosPuntosEntrega->map(function ($m) {
                    return $this->poblacionesTerritoriales['municipios']->where('Codigo', $m)->first();
                })->sum('Valor');

                // poblacion establecimientos que son punto de entrega
                $establecimientosPuntosEntrega = $this->getEstablecimientos()->whereIn('id', $this->puntosEntrega->get('establecimiento', collect([]))->where('region_id', $region->id)->pluck('establecimiento_id'))->pluck('code');
                $totalEstablecimientosPuntosEntrega = $this->sumaPoblacionEstablecimientos($establecimientosPuntosEntrega);

                return [
                    'id' => $region->id,
                    'Codigo'  => $region->code,
                    'Valor' => $totalRegion - $totalServiciosPuntosEntrega - $totalMunicipiosPuntosEntrega - $totalEstablecimientosPuntosEntrega,
                    'puntoEntrega' => $puntosRegiones->first(function ($p) use ($region) {
                        return $p->region_id == $region->id;
                    })->id
                ];
            });
    }

    private function calculaCajas($territorio, $ratio)
    {
        return $ratio > 0 ? round($territorio['Valor'] * $ratio) : 0;
    }

    private function createEstimacion($territorio, $cajas)
    {
        $this->estimaciones->push([
            'punto_entrega_id'   => $territorio['puntoEntrega'],
            'poblacion_asignada' => $territorio['Valor'],
            'producto_id'        => $this->producto->id,
            'cajas_total'        => $cajas,
            'created_at'         => $this->now,
            'updated_at'         => $this->now,
        ]);

        if ($this->estimaciones->count() == self::CHUNK) {
            $this->insertaEstimaciones();
            $this->estimaciones = collect([]);
        }
    }

    private function insertaEstimaciones()
    {
        DB::table('estimaciones')->insert($this->estimaciones->all());
    }

    private function sumaPoblacionEstablecimientos($establecimientos)
    {
        return $this->poblacion->whereIn('Codigo', $establecimientos)->sum('Valor');
    }

    public function poblacionesTerritoriales()
    {
        return $this->poblacionesTerritoriales;
    }

    private function getEstablecimientos()
    {
        if ($this->producto->excluye_hospitales_secundarios) {
            return $this->establecimientosExcluidos;
        }
        return $this->establecimientos;
    }

    private function getComunas()
    {
        if ($this->producto->excluye_hospitales_secundarios) {
            return $this->comunasExcluidos;
        }
        return $this->comunas;
    }

    private function getServicios()
    {
        if ($this->producto->excluye_hospitales_secundarios) {
            return $this->serviciosExcluidos;
        }
        return $this->servicios;
    }

    private function getRegiones()
    {
        if ($this->producto->excluye_hospitales_secundarios) {
            return $this->regionesExcluidos;
        }
        return $this->regiones;
    }
}
