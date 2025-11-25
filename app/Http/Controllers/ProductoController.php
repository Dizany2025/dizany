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
        'codigo_barras'        => 'required|unique:productos,codigo_barras',
        'nombre'               => 'required|string|max:255',
        'descripcion'          => 'nullable|string',
        'precio_compra'        => 'required|numeric|min:0',
        'precio_venta'         => 'required|numeric|min:0',
        'precio_mayor'         => 'nullable|numeric|min:0',
        'unidades_por_mayor'   => 'nullable|integer|min:1',
        'stock'                => 'required|integer|min:0',
        'ubicacion'            => 'nullable|string|max:255',
        'imagen'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        'fecha_vencimiento'    => 'nullable|date',
        'categoria_id'         => 'required|exists:categorias,id',
        'marca_id'             => 'required|exists:marcas,id',
    ]);

        if ($request->hasFile('imagen')) {
        $image = $request->file('imagen');
        $imageName = Str::slug($validated['nombre']) . '-' . time() . '.' . $image->getClientOriginalExtension();

        // Guardar directamente en /public/uploads/productos
        $destinationPath = public_path('uploads/productos');
        $image->move($destinationPath, $imageName);

        $validated['imagen'] = $imageName;
    }

    Producto::create($validated);

    return redirect()->route('productos.create')->with('success', 'Producto creado correctamente.');
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
        'codigo_barras'        => 'required|unique:productos,codigo_barras,' . $id,
        'nombre'               => 'required|string|max:255',
        'descripcion'          => 'nullable|string',
        'precio_compra'        => 'required|numeric|min:0',
        'precio_venta'         => 'required|numeric|min:0',
        'precio_mayor'         => 'nullable|numeric|min:0',
        'unidades_por_paquete'   => 'nullable|integer|min:1',
        'stock'                => 'required|integer|min:0',
        'ubicacion'            => 'nullable|string|max:255',
        'imagen'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
        'fecha_vencimiento'    => 'nullable|date',
        'categoria_id'         => 'required|exists:categorias,id',
        'marca_id'             => 'required|exists:marcas,id',
    ]);

    $producto = Producto::findOrFail($id);

    // Imagen nueva
    if ($request->hasFile('imagen')) {
        // Eliminar imagen anterior
        $rutaAnterior = public_path('uploads/productos/' . $producto->imagen);
        if ($producto->imagen && file_exists($rutaAnterior)) {
            unlink($rutaAnterior);
        }

        $image = $request->file('imagen');
        $imageName = Str::slug($validated['nombre']) . '-' . time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('uploads/productos');
        $image->move($destinationPath, $imageName);

        $validated['imagen'] = $imageName;
    }

    $producto->update($validated);

    return redirect()->route('productos.edit', $producto->id)->with('success', 'Producto actualizado correctamente.');
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
        ->where('activo', 1) // 👈 Solo productos activos
        ->when($searchTerm, function ($query, $searchTerm) {
            return $query->where('nombre', 'like', "%{$searchTerm}%")
                         ->orWhere('codigo_barras', 'like', "%{$searchTerm}%");
        })
        ->limit(10)
        ->get();

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
    // Obtener el producto con las relaciones necesarias
    $producto = Producto::with('categoria', 'marca')->find($id);

    // Verificar si el producto no existe
    if (!$producto) {
        return response()->json([
            'success' => false,
            'message' => 'Producto no encontrado'
        ], 404);
    }

    // Retornar los detalles del producto en formato JSON
    return response()->json([
        'success' => true,  // Asegúrate de devolver 'success' como true
        'id' => $producto->id,
        'codigo_barras' => $producto->codigo_barras,
        'nombre' => $producto->nombre,
        'descripcion' => $producto->descripcion,
        'precio_compra' => $producto->precio_compra,
        'precio_venta' => $producto->precio_venta,
        'precio_mayor' => $producto->precio_mayor,
        'unidades_por_mayor' => $producto->unidades_por_mayor,
        'stock' => $producto->stock,
        'ubicacion' => $producto->ubicacion,
        'imagen' => $producto->imagen, // Solo el nombre del archivo
        'fecha_vencimiento' => $producto->fecha_vencimiento,
        'categoria_nombre' => $producto->categoria ? $producto->categoria->nombre : 'Sin categoría',
        'marca_nombre' => $producto->marca ? $producto->marca->nombre : 'Sin marca',
        'activo' => $producto->activo
    ]);
}

public function parametros()
{
    $marcas = Marca::all();
    $categorias = Categoria::all();
    return view('productos.parametros', compact('marcas', 'categorias'));
}



}
