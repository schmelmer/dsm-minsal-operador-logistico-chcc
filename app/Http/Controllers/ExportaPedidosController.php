<?php

namespace App\Http\Controllers;

use App\Exports\PedidosExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportaPedidosController extends Controller
{
    public function index()
    {
        return Excel::download(new PedidosExport, 'pedidos.xlsx');
    }
}
