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
    public function index(Request $request)
    {
        $tab   = $request->get('tab', 'ingresos');
        $rango = $request->get('rango', 'diario');
        $fecha = $request->get('fecha', Carbon::now()->format('Y-m-d'));

        $query = Movimiento::query();

        /* ==========================
           NORMALIZAR FECHA
        ========================== */

        // Si el datepicker envía "2026-01-01 a 2026-01-07"
        // NO intentamos parsearlo como una sola fecha
        $fechaLimpia = $fecha;

        if (str_contains($fecha, ' a ')) {
            $partes = explode(' a ', $fecha);
            $fechaLimpia = trim($partes[0]); // para diario, mensual y anual
        }

        /* ==========================
           FILTRO POR TABS
        ========================== */
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

        /* ==========================
           FILTRO POR RANGO
        ========================== */

        if ($rango === 'mensual') {

            $fechaCarbon = Carbon::parse($fechaLimpia);

            $query->whereMonth('fecha', $fechaCarbon->month)
                  ->whereYear('fecha', $fechaCarbon->year);

        } elseif ($rango === 'semanal') {

            // normalizamos el separador
            $fecha = str_replace(' to ', ' a ', $fecha);

            [$f1, $f2] = array_pad(explode(' a ', $fecha), 2, $fecha);

            $inicio = Carbon::parse(trim($f1))->startOfDay();
            $fin    = Carbon::parse(trim($f2 ?? $f1))
                        ->endOfDay();

            $query->whereBetween('fecha', [$inicio, $fin]);
            
        }elseif ($rango === 'anual') {

            // si viene "2026-01-01 a 2026-01-07" agarramos solo la primera parte
            if (str_contains($fecha, ' a ')) {
                $fecha = explode(' a ', $fecha)[0];
            }

            $year = Carbon::parse($fecha)->year;

            $query->whereYear('fecha', $year);
        } else {

            // diario
            $query->whereDate('fecha', $fechaLimpia);
        }

        /* ==========================
           BUSCADOR
        ========================== */
        if ($request->filled('buscar')) {
            $query->where('concepto', 'LIKE', '%' . $request->buscar . '%');
        }

        /* ==========================
           LISTADO
        ========================== */
        $movimientos = $query->orderByDesc('fecha')->paginate(15);

        /* ==========================
           KPIs
        ========================== */

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

    public function detalleVenta($id)
    {
        $venta = Venta::with('detalles.producto', 'cliente')->findOrFail($id);

        return response()->json($venta);
    }
}
