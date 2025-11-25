<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Gasto;

class ReporteController extends Controller
{
    public function index(Request $request)
{
    // Obtener fechas del formulario o usar valores por defecto
    $desde = $request->input('desde', now()->startOfMonth()->format('Y-m-d'));
    $hasta = $request->input('hasta', now()->format('Y-m-d'));

    // Total de ventas en el rango
    $ventas = \App\Models\Venta::whereBetween('fecha', [
        $desde . ' 00:00:00',
        $hasta . ' 23:59:59'
    ])->sum('total');

    // Costo total de productos vendidos en ese periodo
    $costo = \DB::table('detalle_ventas')
        ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
        ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
        ->whereBetween('ventas.fecha', [
            $desde . ' 00:00:00',
            $hasta . ' 23:59:59'
        ])
        ->selectRaw('SUM(detalle_ventas.cantidad * productos.precio_compra) AS total_costo')
        ->value('total_costo') ?? 0;

    // Ganancia bruta
    $gananciaBruta = $ventas - $costo;

    // Gastos registrados en ese rango
    $gastos = \App\Models\Gasto::whereBetween('fecha', [$desde, $hasta])->sum('monto');

    // Ganancia neta final
    $gananciaNeta = $gananciaBruta - $gastos;

    // Enviar todo a la vista
    return view('reportes.index', compact(
        'desde',
        'hasta',
        'ventas',
        'costo',
        'gananciaBruta',
        'gastos',
        'gananciaNeta'
    ));
}

public function resumen(Request $request)
{
    $desde = $request->input('desde', now()->startOfMonth()->format('Y-m-d'));
    $hasta = $request->input('hasta', now()->format('Y-m-d'));

    $ventas = Venta::whereBetween('fecha', ["{$desde} 00:00:00","{$hasta} 23:59:59"])
            ->sum('total');

    $costo = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', ["{$desde} 00:00:00","{$hasta} 23:59:59"])
            ->selectRaw('SUM(detalle_ventas.cantidad * productos.precio_compra) AS total_costo')
            ->value('total_costo') ?? 0;

    $gananciaBruta = $ventas - $costo;

    $gastos = Gasto::whereBetween('fecha', ["{$desde} 00:00:00","{$hasta} 23:59:59"])
            ->sum('monto');

    $gananciaNeta = $gananciaBruta - $gastos;

    return response()->json(compact(
        'ventas','costo','gananciaBruta','gastos','gananciaNeta'
    ));
}

}
