<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\User; // <- tu modelo que usa la tabla 'usuarios'
use App\Models\Movimiento;
use Carbon\Carbon;


class GastoController extends Controller
{
    private function soloAdmin()
{
    if (!auth()->check() || !auth()->user()->esAdmin()) {
        abort(403, 'No autorizado');
    }
}


    // Mostrar lista de gastos
    public function index(Request $request)
    {
        $query = Gasto::where('estado', 'activo')
              ->with('usuario'); // Relación con el modelo Usuario

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
        'metodo_pago' => 'required|string|max:50',
    ]);

    // 1️⃣ Guardar gasto
    $gasto = Gasto::create([
        'usuario_id'  => $request->usuario_id,
        'descripcion' => $request->descripcion,
        'monto'       => $request->monto,
        'fecha'       => $request->fecha,
        'metodo_pago' => $request->metodo_pago,
        'estado'      => 'activo', // 👈 CLAVE
    ]);

    // 2️⃣ Registrar movimiento (CLAVE)
    Movimiento::create([
        'fecha'           => now()->toDateString(),
        'hora'            => now()->toTimeString(),
        'tipo'            => 'egreso',
        'subtipo'         => 'gasto', // ✅ CLAVE
        'concepto'        => $request->descripcion,
        'monto'           => $request->monto,
        'metodo_pago'     => $request->metodo_pago ?? 'efectivo',
        'estado'          => 'pagado',
        'referencia_tipo' => 'gasto',
        'referencia_id'   => $gasto->id,
    ]);

    return redirect()
        ->route('gastos.index')
        ->with('success', 'Gasto registrado correctamente.');
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

    public function destroy($id)
        {
            $this->soloAdmin();

            $gasto = Gasto::findOrFail($id);

            // 🔴 Anular gasto
            $gasto->update([
                'estado' => 'anulado'
            ]);

            // 🔴 Anular movimiento asociado
            Movimiento::where('referencia_tipo', 'gasto')
                ->where('referencia_id', $gasto->id)
                ->update([
                    'estado' => 'anulado'
                ]);

            return redirect()
                ->route('gastos.index')
                ->with('success', 'Gasto anulado correctamente');
        }

public function edit($id)
{
    $this->soloAdmin();

    $gasto = Gasto::findOrFail($id);
    $usuarios = User::all();

    return view('gastos.edit', compact('gasto', 'usuarios'));
}
public function update(Request $request, $id)
{
    $this->soloAdmin();
    $request->validate([
        'usuario_id'  => 'required|exists:usuarios,id',
        'descripcion' => 'required|string|max:255',
        'monto'       => 'required|numeric|min:0.01',
        'fecha'       => 'required|date',
        'metodo_pago' => 'nullable|string|max:50',
    ]);

    $gasto = Gasto::findOrFail($id);

    $gasto->update([
        'usuario_id'  => $request->usuario_id,
        'descripcion' => $request->descripcion,
        'monto'       => $request->monto,
        'fecha'       => $request->fecha,
        'metodo_pago' => $request->metodo_pago,
    ]);

    return redirect()
        ->route('gastos.index')
        ->with('success', 'Gasto actualizado correctamente');
}



}
