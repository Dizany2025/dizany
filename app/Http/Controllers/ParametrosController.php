<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;
use App\Models\Categoria;

class ParametrosController extends Controller
{
    // Mostrar vista con marcas y categorías
    public function index()
    {
        $marcas = Marca::all();
        $categorias = Categoria::all();

        return view('productos.parametros', compact('marcas', 'categorias'));
    }

    // Guardar una nueva marca
    public function storeMarca(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Marca::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('productos.parametros')->with('success', 'Marca registrada correctamente.');
    }

    // Eliminar una marca
   public function destroyMarca($id)
{
    try {
        Marca::destroy($id);
        return response()->json(['exito' => true]);
    } catch (\Exception $e) {
        \Log::error('Error al eliminar marca: ' . $e->getMessage());

        if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
            return response()->json(['exito' => false, 'mensaje' => 'No se puede eliminar esta marca porque está siendo utilizada por uno o más productos.']);
        }

        return response()->json(['exito' => false, 'mensaje' => 'Ocurrió un error al eliminar la marca.']);
    }
}


    // Guardar una nueva categoría
    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        Categoria::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('productos.parametros')->with('success', 'Categoría registrada correctamente.');
    }

    // Eliminar una categoría
    public function destroyCategoria($id)
{
    try {
        Categoria::destroy($id);
        return response()->json(['exito' => true]);
    } catch (\Exception $e) {
        \Log::error('Error al eliminar categoría: ' . $e->getMessage());

        if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
            return response()->json(['exito' => false, 'mensaje' => 'No se puede eliminar esta categoría porque está siendo utilizada por uno o más productos.']);
        }

        return response()->json(['exito' => false, 'mensaje' => 'Ocurrió un error al eliminar la categoría.']);
    }
}
public function validarMarca(Request $request)
{
    $existe = \App\Models\Marca::where('nombre', $request->nombre)->exists();
    return response()->json(['existe' => $existe]);
}

public function validarCategoria(Request $request)
{
    $existe = \App\Models\Categoria::where('nombre', $request->nombre)->exists();
    return response()->json(['existe' => $existe]);
}

public function updateMarca(Request $request, $id)
{
    $marca = Marca::findOrFail($id);

    $request->validate([
        'nombre' => 'required|string|max:100',
        'descripcion' => 'nullable|string'
    ]);

    $marca->update([
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion
    ]);

    return redirect()->back()->with('success', 'Marca actualizada correctamente');
}

public function updateCategoria(Request $request, $id)
{
    $categoria = Categoria::findOrFail($id);

    $request->validate([
        'nombre' => 'required|string|max:100'
    ]);

    $categoria->update([
        'nombre' => $request->nombre
    ]);

    return redirect()->back()->with('success', 'Categoría actualizada correctamente');
}


}
