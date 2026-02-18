<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    public function inventario()
{
    $productos_bajos = DB::table('productos')
        ->join('lotes', 'productos.id', '=', 'lotes.producto_id')
        ->where('lotes.activo', 1)
        ->groupBy('productos.id')
        ->havingRaw('SUM(lotes.stock_actual) <= 10')
        ->pluck('productos.id'); // 👈 SOLO trae los IDs

    $stock_bajo = $productos_bajos->count();

    $por_vencer = DB::table('lotes')
        ->where('activo', 1)
        ->where('stock_actual', '>', 0)
        ->whereNotNull('fecha_vencimiento')
        ->whereDate('fecha_vencimiento', '<=', now()->addDays(7))
        ->count();

    return response()->json([
        'stock_bajo' => $stock_bajo,
        'por_vencer' => $por_vencer
    ]);
}


}
