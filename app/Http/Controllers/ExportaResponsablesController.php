<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResponsablesExport;

class ExportaResponsablesController extends Controller
{
    public function index()
    {
        return Excel::download(new ResponsablesExport, 'responsables.xlsx');
    }
}
