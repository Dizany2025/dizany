<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('id', 'desc')->get();
        return view('proveedores.index', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'tipo_documento' => 'required|in:RUC,DNI,OTRO',
            'numero_documento' => 'required|string|max:20|unique:proveedores,numero_documento',
        ]);

        Proveedor::create([
            'nombre' => $request->nombre,
            'tipo_documento' => $request->tipo_documento,
            'numero_documento' => $request->numero_documento,
            'contacto' => $request->contacto,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'direccion' => $request->direccion,
            'estado' => 1
        ]);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor registrado correctamente');
    }
}
