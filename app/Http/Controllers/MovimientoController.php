<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use Carbon\Carbon;

class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        // Filtros base
        $rango = $request->input('rango', 'diario');
        $fecha = $request->input('fecha', now()->toDateString());
        $tab   = $request->input('tab', 'ingresos');

        $query = Movimiento::query();

        // 🗂️ Tabs (igual Treinta)
        match ($tab) {
            'ingresos'   => $query->where('tipo', 'ingreso')->where('estado', 'pagado'),
            'egresos'    => $query->where('tipo', 'egreso')->where('estado', 'pagado'),
            'por_cobrar' => $query->where('tipo', 'ingreso')->where('estado', 'pendiente'),
            'por_pagar'  => $query->where('tipo', 'egreso')->where('estado', 'pendiente'),
            default      => null,
        };

        // 📅 Filtros de fecha
        if ($rango === 'diario') {
            $query->whereDate('fecha', $fecha);
        } elseif ($rango === 'mensual') {
            $query->whereMonth('fecha', Carbon::parse($fecha)->month)
                  ->whereYear('fecha', Carbon::parse($fecha)->year);
        }

        $movimientos = $query->orderByDesc('fecha')->paginate(15);

        // KPIs (RESPETAN FILTROS)
        // =========================
        $ingresos = (clone $query)
            ->where('tipo', 'ingreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $egresos = (clone $query)
            ->where('tipo', 'egreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $balance = $ingresos - $egresos;


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
