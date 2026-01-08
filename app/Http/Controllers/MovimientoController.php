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
            /* ==========================
            PARAMETROS
            ========================== */
            $tab   = $request->get('tab', 'ingresos');
            $rango = $request->get('rango', 'diario');
            $fecha = $request->get('fecha', now()->format('Y-m-d'));

            // normalizar separador semanal
            $fecha = str_replace(' to ', ' a ', $fecha);

            /* ==========================
            QUERY BASE (TABLA)
            ========================== */
            $query = Movimiento::query();

            // -------- FILTRO POR TAB --------
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
            CALCULAR RANGO DE FECHAS
            (UNA SOLA VEZ)
            ========================== */
            $inicio = null;
            $fin    = null;

            if ($rango === 'diario') {

                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                    $fecha = now()->format('Y-m-d');
                }

                $inicio = Carbon::parse($fecha)->startOfDay();
                $fin    = Carbon::parse($fecha)->endOfDay();

            } elseif ($rango === 'semanal') {

                $partes = array_map('trim', explode(' a ', $fecha));
                $f1 = $partes[0] ?? now()->format('Y-m-d');
                $f2 = $partes[1] ?? $f1;

                $inicio = Carbon::parse($f1)->startOfDay();
                $fin    = Carbon::parse($f2)->endOfDay();

            } elseif ($rango === 'mensual') {

                /*
                | Mensual puede venir como:
                | - "2026-01"
                | - "Ene 2026"
                */

                try {
                    // Intentar formato YYYY-MM
                    if (preg_match('/^\d{4}-\d{2}$/', $fecha)) {
                        $carbon = Carbon::createFromFormat('Y-m', $fecha);
                    } else {
                        // Intentar formato "Ene 2026"
                        $carbon = Carbon::createFromLocaleFormat(
                            'M Y',
                            'es',
                            $fecha
                        );
                    }
                } catch (\Exception $e) {
                    // Fallback seguro
                    $carbon = now();
                }

                $inicio = $carbon->copy()->startOfMonth();
                $fin    = $carbon->copy()->endOfMonth();
            } elseif ($rango === 'anual') {

                $year = substr($fecha, 0, 4);
                if (!preg_match('/^\d{4}$/', $year)) {
                    $year = now()->year;
                }

                $inicio = Carbon::create($year, 1, 1)->startOfDay();
                $fin    = Carbon::create($year, 12, 31)->endOfDay();
            }

            // aplicar rango a la TABLA
            if ($inicio && $fin) {
                $query->whereBetween('fecha', [$inicio, $fin]);
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
            $movimientos = $query
                ->orderByDesc('fecha')
                ->paginate(15);

            /* ==========================
            KPIs (MISMO RANGO)
            ========================== */
            $ventas = Movimiento::where('tipo', 'ingreso')
                ->where('estado', 'pagado')
                ->when($inicio && $fin, fn ($q) =>
                    $q->whereBetween('fecha', [$inicio, $fin])
                )
                ->sum('monto');

            $egresos = Movimiento::where('tipo', 'egreso')
                ->where('estado', 'pagado')
                ->when($inicio && $fin, fn ($q) =>
                    $q->whereBetween('fecha', [$inicio, $fin])
                )
                ->sum('monto');

            $balance = $ventas - $egresos;

            $ganancias = DetalleVenta::whereHas('venta', function ($q) use ($inicio, $fin) {
                    $q->where('estado', 'pagado')
                    ->when($inicio && $fin, fn ($q2) =>
                        $q2->whereBetween('fecha', [$inicio, $fin])
                    );
                })
                ->sum('ganancia');

            /* ==========================
            VISTA
            ========================== */
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
