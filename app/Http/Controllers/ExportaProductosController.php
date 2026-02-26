<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportaProductosController extends Controller
{
    public function index()
    {
        return Excel::download(new ProductosExport, 'productos.xlsx');
    }
}
