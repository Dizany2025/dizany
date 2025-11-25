<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\User; // <- tu modelo que usa la tabla 'usuarios'

class GastoController extends Controller
{
    // Mostrar lista de gastos
  public function index(Request $request)
    {
        $query = Gasto::with('usuario'); // Relación con el modelo Usuario

        // Verificar si hay un filtro de fecha, si no, usar la fecha de hoy
        $fecha = $request->fecha ? $request->fecha : date('Y-m-d'); // Si no hay fecha, se usa la fecha de hoy

        // Aplicar filtro de fecha
        if ($fecha) {
            $query->whereDate('fecha', '=', $fecha); 
        }

        // Filtro por descripción
        if ($request->has('descripcion') && $request->descripcion) {
            $descripcion = $request->descripcion;
            $query->where('descripcion', 'like', '%' . $descripcion . '%');
        }

        // Filtro por usuario
        if ($request->has('usuario') && $request->usuario) {
            $usuario = $request->usuario;
            $query->where('usuario_id', '=', $usuario);
        }

        // Obtener los gastos filtrados
        $gastos = $query->paginate(10);
        $usuarios = User::all(); // Para el filtro de usuario

        // Si la solicitud es AJAX, devolver la vista actualizada
        if ($request->ajax()) {
            return view('gastos.index', compact('gastos', 'usuarios'))->render();
        }

        // Devolver la vista completa
        return view('gastos.index', compact('gastos', 'usuarios'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $usuarios = User::all(); // Carga los usuarios de la tabla 'usuarios'
        return view('gastos.create', compact('usuarios'));
    }

    // Guardar gasto en la BD
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        Gasto::create($request->all());
        return redirect()->route('gastos.index')->with('success', 'Gasto registrado correctamente.');
    }

     public function showGastos()
    {
        // Obtén todos los usuarios de la base de datos
        $usuarios = User::all();

        // Obtén los gastos con la relación 'usuario'
        $gastos = Gasto::with('usuario')->paginate(10);

        // Pasa ambas variables a la vista
        return view('gastos.index', compact('gastos', 'usuarios'));
    }

}
