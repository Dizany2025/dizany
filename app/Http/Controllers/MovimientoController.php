<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use PDF;

class MovimientoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTADO PRINCIPAL
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $tab   = $request->get('tab', 'ingresos');
        $rango = $request->get('rango', 'diario');
        $fecha = $request->get('fecha', Carbon::now()->format('Y-m-d'));

        $query = Movimiento::query();

        // ---------- FILTRO POR TABS ----------
        switch ($tab) {

            case 'ingresos':
                $query->where('tipo', 'ingreso')
                      ->where('estado', 'pagado');
            break;

            case 'egresos':
                $query->where('tipo', 'egreso')
                      ->where('estado', 'pagado');
            break;

            case 'por_cobrar':
                $query->where('estado', 'pendiente')
                      ->whereIn('metodo_pago', ['fiado', 'credito']);
            break;

            case 'por_pagar':
                $query->where('tipo', 'egreso')
                      ->where('estado', 'pendiente');
            break;
        }

                // ---------- FILTRO POR FECHA ----------
        if ($rango === 'mensual') {

            $fechaCarbon = Carbon::parse($fecha);
            $query->whereMonth('fecha', $fechaCarbon->month)
                  ->whereYear('fecha', $fechaCarbon->year);

        } elseif ($rango === 'semanal') {

            // normalizamos separadores
            $fecha = str_replace(',', ' to ', $fecha);
            $fecha = str_replace('  ', ' ', $fecha);

            $partes = explode(' to ', trim($fecha));

            // si solo hay una fecha → completamos semana estilo Treinta
            if (count($partes) === 1) {

                $inicio = Carbon::parse($partes[0])->startOfWeek();
                $fin    = Carbon::parse($partes[0])->endOfWeek();

            } else {

                $inicio = Carbon::parse($partes[0])->startOfDay();
                $fin    = Carbon::parse($partes[1])->endOfDay();
            }

            $query->whereBetween('fecha', [$inicio, $fin]);
        } elseif ($rango === 'anual') {

            $year = Carbon::parse($fecha)->year;

            $query->whereBetween('fecha', [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay()
            ]);

        } else { // diario

            $query->whereDate('fecha', $fecha);
        }


        // ---------- BUSCADOR ----------
        if ($request->filled('buscar')) {
            $query->where('concepto', 'LIKE', '%' . $request->buscar . '%');
        }

        // ---------- LISTADO ----------
        $movimientos = $query->orderByDesc('fecha')->paginate(15);

        // ---------- KPIs ----------
        $ventas = Movimiento::where('tipo', 'ingreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $egresos = Movimiento::where('tipo', 'egreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $balance = $ventas - $egresos;

        $ganancias = DetalleVenta::whereHas('venta', function ($q) {
                $q->where('estado', 'pagado');
            })
            ->sum('ganancia');

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

    /*
    |--------------------------------------------------------------------------
    | REPORTE GENERAL DE MOVIMIENTOS
    |--------------------------------------------------------------------------
    */
    public function reporte(Request $request)
    {
        $movimientos = Movimiento::orderByDesc('fecha')->get();

        $ventas = Movimiento::where('tipo', 'ingreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $egresos = Movimiento::where('tipo', 'egreso')
            ->where('estado', 'pagado')
            ->sum('monto');

        $balance = $ventas - $egresos;

        $pdf = \PDF::loadView('movimientos.reporte_pdf', compact(
            'movimientos',
            'ventas',
            'egresos',
            'balance'
        ));

        return $pdf->stream('reporte_movimientos.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | DETALLE PARA OFFCANVAS (VENTA)
    |--------------------------------------------------------------------------
    */
    public function detalleVenta($id)
    {
        $venta = Venta::with('detalles.producto', 'cliente')->findOrFail($id);

        return response()->json($venta);
    }
}
