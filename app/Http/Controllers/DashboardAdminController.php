<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // KPIs principales
        $ingresosHoy = Movimiento::whereDate('fecha', $hoy)
            ->where('tipo', 'ingreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $egresosHoy = Movimiento::whereDate('fecha', $hoy)
            ->where('tipo', 'egreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $totalIngresos = Movimiento::where('tipo', 'ingreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $totalEgresos = Movimiento::where('tipo', 'egreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $balance = $totalIngresos - $totalEgresos;

        $porCobrar = Movimiento::where('tipo', 'ingreso')
            ->where('estado', 'pendiente')
            ->sum('monto');

        $porPagar = Movimiento::where('tipo', 'egreso')
            ->where('estado', 'pendiente')
            ->sum('monto');

        $ultimosMovimientos = Movimiento::orderByDesc('fecha')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'ingresosHoy',
            'egresosHoy',
            'balance',
            'porCobrar',
            'porPagar',
            'ultimosMovimientos'
        ));

    }
}
