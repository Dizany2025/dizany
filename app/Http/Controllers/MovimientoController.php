<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;

class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $tab   = $request->get('tab', 'ingresos');
        $rango = $request->get('rango', null);
        $fecha = $request->get('fecha', null);

        $query = Movimiento::query();

        /*
        |--------------------------------------------------------------------------
        | FILTROS POR TAB
        |--------------------------------------------------------------------------
        */
        if ($tab === 'ingresos') {
            $query->where('tipo', 'ingreso')
                  ->where('estado', 'pagado');

        } elseif ($tab === 'egresos') {
            $query->where('tipo', 'egreso')
                  ->where('estado', 'pagado');

        } elseif ($tab === 'por_cobrar') {
            $query->where('estado', 'pendiente')
                  ->whereIn('metodo_pago', ['fiado', 'credito']);

        } elseif ($tab === 'por_pagar') {
            $query->where('tipo', 'egreso')
                  ->where('estado', 'pendiente');
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO POR FECHA (opcional)
        |--------------------------------------------------------------------------
        */
        if ($fecha) {
            if ($rango === 'mensual') {
                $query->whereMonth('fecha', Carbon::parse($fecha)->month)
                      ->whereYear('fecha', Carbon::parse($fecha)->year);
            } else {
                $query->whereDate('fecha', $fecha);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LISTADO
        |--------------------------------------------------------------------------
        */
        $movimientos = $query->orderByDesc('fecha')->paginate(15);

        /*
        |--------------------------------------------------------------------------
        | KPIs GLOBALES
        |--------------------------------------------------------------------------
        */

        // Total de ventas (ingresos pagados)
        $ventas = Movimiento::where('tipo', 'ingreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        // Total de egresos pagados
        $egresos = Movimiento::where('tipo', 'egreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        // Balance general
        $balance = $ventas - $egresos;

        // Ganancia REAL desde detalle_ventas
        $ganancias = DetalleVenta::whereHas('venta', function ($q) {
                $q->where('estado', 'pagado');
            })
            ->sum('ganancia');

        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */
        return view('movimientos.index', compact(
            'movimientos',
            'ventas',
            'egresos',
            'balance',
            'ganancias',
            'tab',
            'rango',
            'fecha'
        ));
    }
}
