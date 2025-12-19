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
        'precio_caja'          => 'nullable|numeric|min:0',

        // Conversiones
        'unidades_por_paquete' => 'nullable|integer|min:1',
        'paquetes_por_caja'    => 'nullable|integer|min:1',

        // Stock (solo visual, pero validamos)
        'cantidad_ingresada'   => 'required|integer|min:1',
        'nivel_ingreso'        => 'required|in:unidad,paquete,caja',

        'ubicacion'            => 'nullable|string|max:255',
        'imagen'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        'fecha_vencimiento'    => 'nullable|date',
        'categoria_id'         => 'required|exists:categorias,id',
        'marca_id'             => 'nullable|exists:marcas,id',
    ]);

    // ================== CÁLCULO DE STOCK (BACKEND) ==================

    $cantidad = (int) $validated['cantidad_ingresada'];
    $nivel    = $validated['nivel_ingreso'];

    $up = (int) ($validated['unidades_por_paquete'] ?? 0);
    $pc = (int) ($validated['paquetes_por_caja'] ?? 0);

    $stock = 0;

    // UNIDAD
    if ($nivel === 'unidad') {
        $stock = $cantidad;
    }

    // PAQUETE
    if ($nivel === 'paquete') {
        if ($up <= 0) {
            return back()->withErrors([
                'unidades_por_paquete' => 'Debe indicar las unidades por paquete.'
            ])->withInput();
        }
        $stock = $cantidad * $up;
    }

    // CAJA
    if ($nivel === 'caja') {

        // Caja -> Paquete -> Unidad
        if ($pc > 0 && $up > 0) {
            $stock = $cantidad * $pc * $up;
        }
        // Caja -> Unidad directo (vino, aceite)
        elseif ($up > 0) {
            $stock = $cantidad * $up;
        }
        else {
            return back()->withErrors([
                'unidades_por_paquete' => 'Debe indicar cuántas unidades trae la caja.'
            ])->withInput();
        }
    }

    $validated['stock'] = $stock;

    // ================== EXTRAS ==================

    $validated['activo'] = $request->has('activo') ? 1 : 0;
    $validated['visible_en_catalogo'] = $request->has('visible_en_catalogo') ? 1 : 0;
    $validated['slug'] = Str::slug($validated['nombre']);

    // Imagen
    if ($request->hasFile('imagen')) {
        $image = $request->file('imagen');
        $imageName = Str::slug($validated['nombre']) . '-' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/productos'), $imageName);
        $validated['imagen'] = $imageName;
    }

    // Campos SOLO visuales → fuera
    unset($validated['cantidad_ingresada'], $validated['nivel_ingreso']);

    Producto::create($validated);

    return redirect()
        ->route('productos.create')
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
        $producto = Producto::findOrFail($id);

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

            // 👉 ingreso de stock (solo si el usuario lo usa)
            'cantidad_cajas'       => 'nullable|integer|min:1',

            // stock manual (opción A)
            'stock'                => 'nullable|integer|min:0',

            'ubicacion'            => 'nullable|string|max:255',
            'imagen'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
            'fecha_vencimiento'    => 'nullable|date',
            'categoria_id'         => 'required|exists:categorias,id',
            'marca_id'             => 'nullable|exists:marcas,id',
        ]);

        /* =====================================================
        * 🧮 CÁLCULO DE STOCK (SUMAR EN EDITAR)
        * ===================================================== */

        $stockActual = (int) $producto->stock;

        $cajas    = (int) $request->input('cantidad_cajas');
        $paquetes = (int) $request->input('paquetes_por_caja');
        $unidades = (int) $request->input('unidades_por_paquete');

        $nuevoIngreso = 0;

        // Caja → Paquete → Unidad (galletas, cigarro)
        if ($cajas > 0 && $paquetes > 0 && $unidades > 0) {
            $nuevoIngreso = $cajas * $paquetes * $unidades;
        }
        // Caja → Unidad directo (vino, aceite)
        elseif ($cajas > 0 && $unidades > 0 && $paquetes === 0) {
            $nuevoIngreso = $cajas * $unidades;
        }
        // Paquete → Unidad (fardos)
        elseif ($cajas === 0 && $paquetes > 0 && $unidades > 0) {
            $nuevoIngreso = $paquetes * $unidades;
        }

        if ($nuevoIngreso > 0) {
            // 👉 SUMAR al stock actual
            $validated['stock'] = $stockActual + $nuevoIngreso;
        } else {
            // 👉 Stock manual o sin cambios
            $validated['stock'] = $request->input('stock', $stockActual);
        }

        /* =====================================================
        * ESTADO / SLUG
        * ===================================================== */

        $validated['activo'] = $request->has('activo') ? 1 : 0;
        $validated['visible_en_catalogo'] = $request->has('visible_en_catalogo') ? 1 : 0;

        // 🔒 Slug NO se cambia en update
        $validated['slug'] = $producto->slug;

        /* =====================================================
        * 🖼️ IMAGEN
        * ===================================================== */

        if ($request->hasFile('imagen')) {

            // borrar imagen anterior
            if ($producto->imagen && file_exists(public_path('uploads/productos/' . $producto->imagen))) {
                unlink(public_path('uploads/productos/' . $producto->imagen));
            }

            $image = $request->file('imagen');
            $imageName = Str::slug($validated['nombre']) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/productos'), $imageName);

            $validated['imagen'] = $imageName;
        }

        /* =====================================================
        * LIMPIEZA (campos que no existen en BD)
        * ===================================================== */

        unset($validated['cantidad_cajas']);

        /* =====================================================
        * ACTUALIZAR
        * ===================================================== */

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

public function ordenar(Request $request)
{
    $tipo = $request->tipo;

    $query = Producto::query()->where('activo', 1);

    switch ($tipo) {

        case 'az':
            $query->orderBy('nombre', 'asc');
            break;

        case 'za':
            $query->orderBy('nombre', 'desc');
            break;

        case 'precio_mayor':
            $query->orderBy('precio_venta', 'desc');
            break;

        case 'precio_menor':
            $query->orderBy('precio_venta', 'asc');
            break;

        case 'stock_mayor':
            $query->orderBy('stock', 'desc');
            break;

        case 'stock_menor':
            $query->orderBy('stock', 'asc');
            break;

        case 'mas_vendidos':
            $query->withSum('detalles as total_vendido', 'cantidad')
                  ->orderBy('total_vendido', 'desc');
            break;

        case 'menos_vendidos':
            $query->withSum('detalles as total_vendido', 'cantidad')
                  ->orderBy('total_vendido', 'asc');
            break;

        case 'fecha_asc':   // ⭐ AÑADIR
            $query->orderBy('created_at', 'asc');
            break;

        case 'fecha_desc':  // ⭐ AÑADIR
            $query->orderBy('created_at', 'desc');
            break;

        default:
            $query->orderBy('created_at', 'desc');
    }

    return response()->json($query->get());
}



}
