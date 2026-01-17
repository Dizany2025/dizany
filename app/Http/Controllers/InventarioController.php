<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Lote;
use App\Models\Movimiento;

class InventarioController extends Controller
{
    public function stock()
{
    $stock_bajo = Producto::where('stock', '<=', 10)->orderBy('stock', 'asc')->get();
    $proximos_a_vencer = Producto::whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
        ->orderBy('fecha_vencimiento', 'asc')->get();

    $categorias = Categoria::orderBy('nombre')->get();

    return view('inventario.stock', compact('stock_bajo', 'proximos_a_vencer', 'categorias'));
}
public function actualizarStock(Request $request, $id)
{
    $producto = Producto::findOrFail($id);
    $producto->stock = $request->input('stock');
    $producto->save();

    return response()->json(['success' => true]);
}

public function obtenerNotificaciones()
{
    $stock_bajo = Producto::where('stock', '<=', 10)->count();

    $proximos_a_vencer = Producto::whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<=', now()->addDays(30))
        ->count();

    return response()->json([
        'stock_bajo' => $stock_bajo,
        'por_vencer' => $proximos_a_vencer
    ]);
}


public function lote()
{
    $productos = Producto::where('activo', 1)
        ->orderBy('nombre')
        ->get();

    $proveedores = Proveedor::where('estado', 'activo')
        ->orderBy('nombre')
        ->get();

    return view('inventario.lote', compact('productos', 'proveedores'));
}


}
