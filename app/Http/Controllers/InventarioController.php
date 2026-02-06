<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Movimiento;
use App\Models\Lote;
use App\Models\LoteMovimiento;

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
    $stock_bajo = Producto::where('stock_unidades', '<=', 10)->count();

    $proximos_a_vencer = Producto::whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<=', now()->addDays(30))
        ->count();

    return response()->json([
        'stock_bajo' => $stock_bajo,
        'por_vencer' => $proximos_a_vencer
    ]);
}

public function lotes()
{
    $lotes = Lote::with('producto')
        ->orderBy('fecha_ingreso', 'desc')
        ->get();

    return view('inventario.lotes_index', compact('lotes'));
}
public function lote()
{
    $productos = Producto::where('activo', 1)
        ->orderBy('nombre')
        ->get();

    $proveedores = Proveedor::orderBy('nombre')->get();

    return view('inventario.lote', compact('productos', 'proveedores'));
}


public function storeLote(Request $request)
{
    $request->validate([
        'producto_id'       => 'required|exists:productos,id',
        'proveedor_id'      => 'nullable|exists:proveedores,id',
        'stock_inicial'     => 'required|integer|min:1',
        'precio_compra'     => 'required|numeric|min:0',
        'precio_unidad'     => 'required|numeric|min:0',
        'precio_paquete'    => 'nullable|numeric|min:0',
        'precio_caja'       => 'nullable|numeric|min:0',
        'fecha_ingreso'     => 'required|date',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_ingreso',
    ]);

    $lote = Lote::create([
        'producto_id'       => $request->producto_id,
        'proveedor_id'      => $request->proveedor_id,
        'fecha_ingreso'     => $request->fecha_ingreso,
        'fecha_vencimiento' => $request->fecha_vencimiento,
        'stock_inicial'     => $request->stock_inicial,
        'stock_actual'      => $request->stock_inicial, // 🔥 CLAVE
        'precio_compra'     => $request->precio_compra,
        'precio_unidad'     => $request->precio_unidad,
        'precio_paquete'    => $request->precio_paquete,
        'precio_caja'       => $request->precio_caja,
        'activo'            => 1,
    ]);

    return redirect()
        ->route('inventario.lotes')
        ->with('success', 'Lote registrado correctamente');
}

    public function edit(Lote $lote)
    {
        return view('inventario.lote_edit', compact('lote'));
    }

public function update(Request $request, Lote $lote)
{
    $request->validate([
        'fecha_vencimiento' => 'nullable|date',
        'precio_unidad'     => 'nullable|numeric|min:0',
        'precio_paquete'    => 'nullable|numeric|min:0',
        'precio_caja'       => 'nullable|numeric|min:0',
    ]);

    $lote->update([
        'fecha_vencimiento' => $request->fecha_vencimiento,
        'precio_unidad'     => $request->precio_unidad,
        'precio_paquete'    => $request->precio_paquete,
        'precio_caja'       => $request->precio_caja,
    ]);

    return redirect()
        ->route('inventario.lotes')
        ->with('success', 'Lote actualizado correctamente');
}
    

public function ajustarStock(Request $request, Lote $lote)
{
    $request->validate([
        'tipo'     => 'required|in:sumar,restar',
        'cantidad' => 'required|integer|min:1',
        'motivo'   => 'required|string|max:255',
    ]);

    $stockAntes = $lote->stock_actual;

    if ($request->tipo === 'restar') {
        if ($request->cantidad > $stockAntes) {
            return response()->json([
                'message' => 'No puedes restar más stock del disponible'
            ], 422);
        }
        $nuevoStock = $stockAntes - $request->cantidad;
    } else {
        $nuevoStock = $stockAntes + $request->cantidad;
    }

    // Actualizar stock
    $lote->update([
        'stock_actual' => $nuevoStock
    ]);

    // Registrar movimiento de AJUSTE
    LoteMovimiento::create([
        'lote_id'       => $lote->id,
        'usuario_id'    => auth()->id(),
        'tipo'          => 'ajuste',
        'cantidad'      => $request->cantidad,
        'stock_antes'   => $stockAntes,
        'stock_despues' => $nuevoStock,
        'motivo'        => $request->motivo,
        'creado_en'     => now(),
    ]);

    return response()->json([
        'message' => 'Ajuste aplicado correctamente',
        'stock'   => $nuevoStock
    ]);
}

public function movimientos(Lote $lote)
{
    $movimientos = $lote->movimientos()
        ->with('usuario')
        ->orderBy('creado_en', 'desc')
        ->paginate(10);

    return view('inventario.lote_movimientos', compact('lote', 'movimientos'));
}


}
