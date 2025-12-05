<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $categoria_id = $request->input('categoria_id');
        $marca_id = $request->input('marca_id');
        $search = $request->input('search');

        extract($this->obtenerCategoriasYMarcas());

        $query = Producto::query();
        // Cargar las relaciones de categoria y marca
        $query->with('categoria', 'marca');  // Añadido

        if ($categoria_id && $categoria_id != 'todos') {
            $query->where('categoria_id', $categoria_id);
        }

        if ($marca_id && $marca_id != 'todos') {
            $query->where('marca_id', $marca_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo_barras', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        $productos = $query->orderBy('id', 'desc')->paginate(10);

        return view('productos.index', compact('productos', 'categorias', 'marcas'));
    }

    public function create()
    {
        extract($this->obtenerCategoriasYMarcas());

        return view('productos.create', compact('categorias', 'marcas'));
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'codigo_barras'        => 'nullable|string|max:50|unique:productos,codigo_barras',
        'nombre'               => 'required|string|max:255',
        'descripcion'          => 'nullable|string',
        'precio_compra'        => 'required|numeric|min:0',
        'precio_venta'         => 'required|numeric|min:0',

        'precio_paquete'       => 'nullable|numeric|min:0',
        'unidades_por_paquete' => 'nullable|integer|min:1',
        'paquetes_por_caja'    => 'nullable|integer|min:1',
        'tipo_paquete'         => 'nullable|string|max:50',
        'precio_caja'          => 'nullable|numeric|min:0',

        'cantidad_cajas'       => 'nullable|integer|min:1',   // SOLO para cálculo, no está en BD
        'stock'                => 'nullable|integer|min:0',

        'ubicacion'            => 'nullable|string|max:255',
        'imagen'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        'fecha_vencimiento'    => 'nullable|date',
        'categoria_id'         => 'required|exists:categorias,id',
        'marca_id'             => 'nullable|exists:marcas,id',   // la dejo opcional para evitar error
    ]);

    // ==== CÁLCULO DE STOCK AUTOMÁTICO ====
    $cajas     = $request->input('cantidad_cajas');       // cuántas cajas compraste
    $paquetes  = $request->input('paquetes_por_caja');    // cuántos paquetes trae cada caja
    $unidades  = $request->input('unidades_por_paquete'); // cuántas unidades trae cada paquete

    $stock_total = 0;

    if ($cajas && $paquetes && $unidades) {
        // Caso 1: Caja -> Paquete -> Unidad  (galletas, cigarro, etc.)
        $stock_total = $cajas * $paquetes * $unidades;
    } elseif ($cajas && $unidades && !$paquetes) {
        // Caso 2: Caja -> Unidad directo (vino: caja de 12 botellas)
        $stock_total = $cajas * $unidades;
    } elseif ($paquetes && $unidades && !$cajas) {
        // Caso 3: Solo Paquete -> Unidad (agua/gaseosa en fardos, sin caja)
        $stock_total = $paquetes * $unidades;
    } else {
        // Caso 4: stock manual
        $stock_total = $request->input('stock', 0);
    }

    $validated['stock'] = $stock_total;

    // Activo / visible
    $validated['activo'] = $request->has('activo') ? 1 : 0;
    $validated['visible_en_catalogo'] = $request->has('visible_en_catalogo') ? 1 : 0;

    // Slug
    $validated['slug'] = Str::slug($validated['nombre']);

    // Imagen
    if ($request->hasFile('imagen')) {
        $image = $request->file('imagen');
        $imageName = Str::slug($validated['nombre']) . '-' . time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('uploads/productos');
        $image->move($destinationPath, $imageName);
        $validated['imagen'] = $imageName;
    }

    // Este campo NO existe en la BD
    unset($validated['cantidad_cajas']);

    Producto::create($validated);

    return redirect()->route('productos.create')
        ->with('success', 'Producto creado correctamente.');
}



    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        extract($this->obtenerCategoriasYMarcas());

        return view('productos.edit', compact('producto', 'categorias', 'marcas'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'codigo_barras'        => 'nullable|string|max:50|unique:productos,codigo_barras,' . $id,
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',

            'precio_compra'        => 'required|numeric|min:0',
            'precio_venta'         => 'required|numeric|min:0',

            'precio_paquete'       => 'nullable|numeric|min:0',
            'unidades_por_paquete' => 'nullable|integer|min:1',
            'paquetes_por_caja'    => 'nullable|integer|min:1',
            'tipo_paquete'         => 'nullable|string|max:50',
            'precio_caja'          => 'nullable|numeric|min:0',

            'cantidad_cajas'       => 'nullable|integer|min:1',  // solo cálculo
            'stock'                => 'nullable|integer|min:0',

            'ubicacion'            => 'nullable|string|max:255',
            'imagen'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
            'fecha_vencimiento'    => 'nullable|date',
            'categoria_id'         => 'required|exists:categorias,id',
            'marca_id'             => 'nullable|exists:marcas,id',
        ]);

        $producto = Producto::findOrFail($id);

        // ==========
        // 🧮 CALCULAR STOCK
        // ==========
        $cajas     = $request->input('cantidad_cajas');
        $paquetes  = $request->input('paquetes_por_caja');
        $unidades  = $request->input('unidades_por_paquete');

        if ($cajas && $paquetes && $unidades) {
            // Caja → Paquete → Unidad
            $validated['stock'] = $cajas * $paquetes * $unidades;
        } elseif ($cajas && $unidades && !$paquetes) {
            // Caja → Unidad (vino)
            $validated['stock'] = $cajas * $unidades;
        } elseif ($paquetes && $unidades && !$cajas) {
            // Paquete → Unidad (fardos)
            $validated['stock'] = $paquetes * $unidades;
        } else {
            // Stock manual
            $validated['stock'] = $request->input('stock', $producto->stock);
        }

        // ==========
        // ESTADO CHECKBOX
        // ==========
        $validated['activo'] = $request->has('activo') ? 1 : 0;
        $validated['visible_en_catalogo'] = $request->has('visible_en_catalogo') ? 1 : 0;

        // ==========
        // SLUG — NO CAMBIAR EN UPDATE
        // ==========
        $validated['slug'] = $producto->slug;

        // ==========
        // IMAGEN NUEVA
        // ==========
        if ($request->hasFile('imagen')) {
            // borrar anterior
            if ($producto->imagen && file_exists(public_path('uploads/productos/' . $producto->imagen))) {
                unlink(public_path('uploads/productos/' . $producto->imagen));
            }

            $image = $request->file('imagen');
            $imageName = Str::slug($validated['nombre']) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/productos'), $imageName);

            $validated['imagen'] = $imageName;
        }

        // No guardar este campo (no existe en BD)
        unset($validated['cantidad_cajas']);

        // ==========
        // ACTUALIZAR PRODUCTO
        // ==========
        $producto->update($validated);

        return redirect()
            ->route('productos.edit', $producto->id)
            ->with('success', 'Producto actualizado correctamente.');
    }


public function toggleEstado($id)
{
    $producto = Producto::findOrFail($id);
    $producto->activo = !$producto->activo; // ← corregido
    $producto->save();

    return redirect()->route('productos.index')->with('estado_actualizado', $producto->activo ? 'activado' : 'desactivado');

}

   public function buscar(Request $request)
{
    $searchTerm = $request->input('search');

    $productos = Producto::query()
        ->where('activo', 1)
        ->when($searchTerm, function ($query, $searchTerm) {
            return $query->where('nombre', 'like', "%{$searchTerm}%")
                         ->orWhere('codigo_barras', 'like', "%{$searchTerm}%");
        })
        ->limit(10)
        ->get([
            'id',
            'nombre',
            'descripcion',
            'precio_venta',
            'precio_paquete',
            'unidades_por_paquete',
            'precio_caja',
            'paquetes_por_caja',
            'stock',
            'imagen'
        ]);

    return response()->json($productos);
}

    // validar si el código de barras existe
    public function validarCodigoBarras(Request $request)
    {
        $codigo_barras = $request->input('codigo_barras');

        // Verificar si el código de barras existe
        $exists = Producto::where('codigo_barras', $codigo_barras)->exists();

        // Devolver un valor booleano si existe o no
        return response()->json(['exists' => $exists]);
    }
   // Validar si el código de barras existe, pero excluir el producto actual si estamos editando
   public function validarCodigoBarrasEdicion(Request $request)
    {
        $codigo_barras = $request->input('codigo_barras');
        $producto_id = $request->input('producto_id');  // Obtener el ID del producto si estamos editando

        // Verificar si el código de barras existe, pero excluir el producto actual (si estamos editando)
        $exists = Producto::where('codigo_barras', $codigo_barras)
                        ->where('id', '!=', $producto_id)  // Excluir el producto actual si estamos editando
                        ->exists();
        // Devolver un valor booleano si existe o no
        return response()->json(['exists' => $exists]);
    }
    /**
     * Función privada reutilizable para obtener categorías y marcas
     */
    private function obtenerCategoriasYMarcas()
    {
        return [
            'categorias' => Categoria::all(),
            'marcas' => Marca::all(),
        ];
    }

    public function mostrarDetalles($id)
    {
        // Obtener producto con relaciones
        $producto = Producto::with('categoria', 'marca')->find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,

            'id'                    => $producto->id,
            'codigo_barras'         => $producto->codigo_barras,
            'nombre'                => $producto->nombre,
            'slug'                  => $producto->slug,
            'descripcion'           => $producto->descripcion,

            'precio_compra'         => $producto->precio_compra,
            'precio_venta'          => $producto->precio_venta,
            'precio_paquete'        => $producto->precio_paquete,
            'unidades_por_paquete'  => $producto->unidades_por_paquete,
            'paquetes_por_caja'     => $producto->paquetes_por_caja,
            'precio_caja'           => $producto->precio_caja,
            'tipo_paquete'          => $producto->tipo_paquete,

            'stock'                 => $producto->stock,
            'ubicacion'             => $producto->ubicacion,
            'imagen'                => $producto->imagen,
            'fecha_vencimiento'     => $producto->fecha_vencimiento,

            'categoria_nombre'      => $producto->categoria ? $producto->categoria->nombre : 'Sin categoría',
            'marca_nombre'          => $producto->marca ? $producto->marca->nombre : 'Sin marca',

            'activo'                => $producto->activo ? 'Sí' : 'No',
            'visible_en_catalogo'   => $producto->visible_en_catalogo ? 'Sí' : 'No',
        ]);
    }

public function parametros()
{
    $marcas = Marca::all();
    $categorias = Categoria::all();
    return view('productos.parametros', compact('marcas', 'categorias'));
}
public function productosIniciales() {
    return Producto::orderBy('nombre')->get();
}



}
