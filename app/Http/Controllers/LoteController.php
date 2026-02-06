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
        $validated = $request->validate([
            'producto_id'       => 'required|exists:productos,id',
            'proveedor_id'      => 'nullable|exists:proveedores,id',
            'codigo_lote' => 'nullable|string|max:100|unique:lotes,codigo_lote',
            'stock_inicial'     => 'required|integer|min:1',
            'precio_compra'     => 'required|numeric|min:0',
            'precio_unidad'     => 'required|numeric|min:0',
            'precio_paquete'    => 'nullable|numeric|min:0',
            'precio_caja'       => 'nullable|numeric|min:0',
            'fecha_ingreso'     => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_ingreso',
        ]);


        Lote::create([
            'producto_id'       => $validated['producto_id'],
            'proveedor_id'      => $validated['proveedor_id'] ?? null,
            'codigo_lote'       => $validated['codigo_lote'] ?? null,
            'fecha_ingreso'     => $validated['fecha_ingreso'],
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            'stock_inicial'     => $validated['stock_inicial'],
            'stock_actual'      => $validated['stock_inicial'], // FEFO correcto
            'precio_compra'     => $validated['precio_compra'],
            'precio_unidad'     => $validated['precio_unidad'],
            'precio_paquete'    => $validated['precio_paquete'] ?? null,
            'precio_caja'       => $validated['precio_caja'] ?? null,
            'activo'            => 1,
        ]);

        return redirect()
            ->route('inventario.lotes')
            ->with('success', 'Lote registrado correctamente');
    }

    

}
