<?php

namespace App\Http\Controllers;

use App\Exports\ReporteGeneralExport;
use App\Models\Gasto;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GastoController extends Controller
{
    public function index()
    {
        // Traemos los gastos del día actual
        $gastos = Gasto::whereDate('created_at', today())->orderBy('id', 'desc')->get();
        $totalDia = $gastos->sum('monto');

        return view('admin.gastos.index', compact('gastos', 'totalDia'));
    }

    public function store(Request $request)
    {
        Gasto::create([
            'descripcion' => strtoupper($request->descripcion),
            'monto' => $request->monto,
            'categoria' => $request->categoria ?? 'GENERAL',
            'usuario' => auth()->user()->nombre ?? 'Admin', // BUG 1 CORREGIDO: campo es 'nombre', no 'name'
        ]);

        return back()->with('success', 'Gasto registrado correctamente');
    }

    public function descargarReporte()
    {
        $fecha = now()->format('d-m-Y');

        return Excel::download(new ReporteGeneralExport, "Reporte_General_{$fecha}.xlsx");
    }
}