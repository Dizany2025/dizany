<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Movimiento;
use App\Models\Lote;
use App\Models\LoteMovimiento;

class InventarioController extends Controller
{
    public function lotes()
{
    $lotes = Lote::with(['producto', 'proveedor'])
        ->withCount('movimientos')
        ->where('activo', 1)
        ->orderByRaw('fecha_vencimiento IS NULL') // los sin vencimiento al final
        ->orderBy('fecha_vencimiento', 'asc')    // FEFO REAL
        ->orderBy('fecha_ingreso', 'asc')        // desempate
        ->get();

        $productos = Producto::where('activo', 1)->orderBy('nombre')->get();

    return view('inventario.lotes_index', compact('lotes', 'productos'));
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
        'codigo_comprobante'       => 'nullable|string|max:100', // 👈 AÑADIDO
        'stock_inicial'     => 'required|integer|min:1',
        'precio_compra'     => 'required|numeric|min:0',
        'precio_unidad'     => 'required|numeric|min:0',
        'precio_paquete'    => 'nullable|numeric|min:0',
        'precio_caja'       => 'nullable|numeric|min:0',
        'fecha_ingreso'     => 'required|date',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_ingreso',
    ]);

        DB::transaction(function () use ($request) {

        $ultimoNumero = Lote::where('producto_id', $request->producto_id)
            ->lockForUpdate()
            ->max('numero_lote');

        $numeroLote = ($ultimoNumero ?? 0) + 1;

        Lote::create([
            'producto_id'       => $request->producto_id,
            'proveedor_id'      => $request->proveedor_id,
            'numero_lote'       => $numeroLote, // 👈 AQUÍ
            'codigo_comprobante'=> $request->codigo_comprobante,
            'fecha_ingreso'     => $request->fecha_ingreso,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'stock_inicial'     => $request->stock_inicial,
            'stock_actual'      => $request->stock_inicial,
            'precio_compra'     => $request->precio_compra,
            'precio_unidad'     => $request->precio_unidad,
            'precio_paquete'    => $request->precio_paquete,
            'precio_caja'       => $request->precio_caja,
            'activo'            => 1,
        ]);
    });
        return redirect()
            ->route('inventario.lote')
            ->with('success', 'Lote registrado correctamente');
    
}

    public function edit(Lote $lote)
    {
        return view('inventario.lote_edit', compact('lote'));
    }

public function update(Request $request, Lote $lote)
{
    $request->validate([
        'codigo_comprobante'        => 'nullable|string|max:100',
        'fecha_vencimiento' => 'nullable|date',
        'precio_unidad'     => 'nullable|numeric|min:0',
        'precio_paquete'    => 'nullable|numeric|min:0',
        'precio_caja'       => 'nullable|numeric|min:0',
    ]);

    $lote->update([
        'codigo_comprobante'        => $request->codigo_comprobante,
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
