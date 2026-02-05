<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    /* ===============================
       LISTAR LOTES
    =============================== */
    public function index()
    {
        $lotes = Lote::with(['producto', 'proveedor'])
        ->orderByRaw('fecha_vencimiento IS NULL')
        ->orderBy('fecha_vencimiento', 'asc')
        ->orderBy('fecha_ingreso', 'asc')
        ->get();

        return view('inventario.lotes_index', compact('lotes'));
    }

    /* ===============================
       FORMULARIO INGRESO LOTE
    =============================== */
    public function create()
    {
        $productos = Producto::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('inventario.lote', compact('productos', 'proveedores'));
    }

    /* ===============================
       GUARDAR LOTE
    =============================== */
    public function store(Request $request)
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

        Lote::create([
            'producto_id'       => $request->producto_id,
            'proveedor_id'      => $request->proveedor_id,
            'fecha_ingreso'     => $request->fecha_ingreso,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'stock_inicial'     => $request->stock_inicial,
            'stock_actual'      => $request->stock_inicial, // 🔥 clave FIFO
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

    

}
