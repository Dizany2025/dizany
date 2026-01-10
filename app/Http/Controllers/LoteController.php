<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;

class LoteController extends Controller
{
    public function index()
    {
        $lotes = Lote::with(['producto', 'proveedor'])
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('inventario.lotes_index', compact('lotes'));
    }

    public function create()
    {
        $productos = Producto::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $proveedores = Proveedor::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('inventario.lote', compact('productos', 'proveedores'));
    }

    public function store(Request $request)
    {
        // 1) Validación base
        $rules = [
            'producto_id'    => 'required|exists:productos,id',
            'proveedor_id'   => 'nullable|exists:proveedores,id',
            'cantidad'       => 'required|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0',
            'precio_venta'   => 'required|numeric|min:0',
            'fecha_ingreso'  => 'required|date',
            'metodo_pago'    => 'required|string|max:50',
        ];

        $messages = [
            'producto_id.required' => 'Seleccione un producto.',
            'producto_id.exists'   => 'El producto seleccionado no existe.',
            'cantidad.required'    => 'Ingrese la cantidad.',
            'cantidad.min'         => 'La cantidad debe ser mayor a 0.',
            'fecha_ingreso.date'   => 'La fecha de ingreso no es válida.',
        ];

        // 2) Traer producto para saber si maneja vencimiento
        $producto = Producto::select('id', 'maneja_vencimiento')->findOrFail($request->producto_id);

        // 3) Regla inteligente según el producto
        if ((int)$producto->maneja_vencimiento === 1) {
            $rules['fecha_vencimiento'] = 'required|date|after_or_equal:fecha_ingreso';
            $messages['fecha_vencimiento.required'] = 'Este producto requiere fecha de vencimiento.';
            $messages['fecha_vencimiento.after_or_equal'] = 'El vencimiento no puede ser anterior al ingreso.';
        } else {
            // Si no maneja vencimiento, permitimos null (si viene algo, debe ser fecha)
            $rules['fecha_vencimiento'] = 'nullable|date';
        }

        $validated = $request->validate($rules, $messages);

        // 4) Normalizar: si no maneja vencimiento, guardamos NULL
        if ((int)$producto->maneja_vencimiento !== 1) {
            $validated['fecha_vencimiento'] = null;
        }

        // 5) Crear lote
        Lote::create([
            'producto_id'       => $validated['producto_id'],
            'proveedor_id'      => $validated['proveedor_id'] ?? null,
            'cantidad'          => $validated['cantidad'],
            'costo_unitario'    => $validated['costo_unitario'],
            'precio_venta'      => $validated['precio_venta'],
            'fecha_ingreso'     => $validated['fecha_ingreso'],
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
            'estado'            => 'activo',
        ]);

        return redirect()
            ->route('inventario.lote')
            ->with('success', 'Lote registrado correctamente.');
    }
}
