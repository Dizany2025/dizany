<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function ajaxStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        // Verificar si ya existe
        if (Marca::where('nombre', strtoupper($request->nombre))->exists()) {
            return response()->json([
                'error' => true,
                'message' => 'La marca ya existe.'
            ]);
        }

        // Crear marca
        $marca = Marca::create([
            'nombre' => strtoupper($request->nombre)
        ]);

        return response()->json([
            'error' => false,
            'data'  => $marca
        ]);
    }

}
?>