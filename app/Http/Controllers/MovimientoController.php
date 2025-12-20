<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use Carbon\Carbon;

class MovimientoController extends Controller
{
    public function index(Request $request)
{
    $tab   = $request->get('tab', 'ingresos');
    $rango = $request->get('rango', null);
    $fecha = $request->get('fecha', null);

    $query = Movimiento::query();

    // Tabs
    if ($tab === 'ingresos') {
        $query->where('tipo', 'ingreso')->where('estado', 'pagado');
    } elseif ($tab === 'egresos') {
        $query->where('tipo', 'egreso')->where('estado', 'pagado');
    } elseif ($tab === 'por_cobrar') {
        $query->where('tipo', 'ingreso')->where('estado', 'pendiente');
    } elseif ($tab === 'por_pagar') {
        $query->where('tipo', 'egreso')->where('estado', 'pendiente');
    }

    // Fecha SOLO si viene
    if ($fecha) {
        if ($rango === 'mensual') {
            $query->whereMonth('fecha', Carbon::parse($fecha)->month)
                  ->whereYear('fecha', Carbon::parse($fecha)->year);
        } else {
            $query->whereDate('fecha', $fecha);
        }
    }

    $movimientos = $query->orderByDesc('fecha')->paginate(15);

    // KPIs globales
    $ingresos = Movimiento::where('tipo', 'ingreso')->where('estado', 'pagado')->sum('monto');
    $egresos  = Movimiento::where('tipo', 'egreso')->where('estado', 'pagado')->sum('monto');
    $balance  = $ingresos - $egresos;

    return view('movimientos.index', compact(
        'movimientos',
        'ingresos',
        'egresos',
        'balance',
        'tab',
        'rango',
        'fecha'
    ));
}

}
