<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PuntosEntregaExport;

class ExportaPuntoEntregaController extends Controller
{
    public function index()
    {
        return Excel::download(new PuntosEntregaExport, 'puntos_de_entrega.xlsx');
    }
}
