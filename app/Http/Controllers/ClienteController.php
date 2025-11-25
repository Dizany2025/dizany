<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    // Método para buscar cliente por DNI o RUC
    public function buscarCliente($dniRuc)
    {
        // Buscar cliente en la base de datos por DNI o RUC
        $cliente = Cliente::where('dni', $dniRuc)
                          ->orWhere('ruc', $dniRuc)
                          ->first();

        if ($cliente) {
            // Si el cliente existe, retornar los datos
            return response()->json([
                'encontrado' => true,
                'nombre' => $cliente->nombre,
                'direccion' => $cliente->direccion,
                'telefono' => $cliente->telefono,
                'ruc' => $cliente->ruc,
                'dni' => $cliente->dni,
            ]);
        }

        // Si el cliente no existe
        return response()->json(['encontrado' => false]);
    }

    // Método para guardar un nuevo cliente
    public function guardar(Request $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validate([
                'dni_ruc'       => 'required|string|max:11',
                'razon_social'  => 'required|string|max:255',
                'direccion'     => 'nullable|string|max:255',
            ]);

            $cliente = new Cliente();

            // Asignar DNI o RUC según longitud
            if (strlen($data['dni_ruc']) === 8) {
                $cliente->dni = $data['dni_ruc'];
            } elseif (strlen($data['dni_ruc']) === 11) {
                $cliente->ruc = $data['dni_ruc'];
            } else {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Número de documento no válido'
                ], 422);
            }

            // Asignar los demás campos
            $cliente->nombre = $data['razon_social'];
            $cliente->direccion = $data['direccion'] ?? null;

            $cliente->save();

            return response()->json(['exito' => true]);

        } catch (\Exception $e) {
            Log::error('Error al guardar cliente: ' . $e->getMessage());

            return response()->json([
                'exito' => false,
                'mensaje' => 'Error interno al guardar el cliente.'
            ], 500);
        }
    }

    // En tu ClienteController.php
   public function index(Request $request)
    {
        $query = Cliente::query();

        // Si hay un parámetro de búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where('nombre', 'like', "%$search%")
                ->orWhere('ruc', 'like', "%$search%")
                ->orWhere('dni', 'like', "%$search%");
        }

        // Paginar los resultados
        $clientes = $query->paginate(10);

        // Si la solicitud es AJAX, devolver solo la vista de clientes (index)
        if ($request->ajax()) {
            return response()->json(view('clientes.index', compact('clientes'))->render());
        }

        return view('clientes.index', compact('clientes'));
    }
  


    public function show($id)
    {
        $cliente = Cliente::findOrFail($id); // Encuentra el cliente por ID
        return view('clientes.show', compact('cliente'));
    }

    public function edit($id)
{
    $cliente = Cliente::findOrFail($id);
    return response()->json($cliente); // Retorna los datos del cliente como respuesta JSON
}

    public function update(Request $request, $id)
{
    // Validación de los datos del formulario
    $validated = $request->validate([
        'client_name' => 'required|string|max:255',
        'client_address' => 'nullable|string',
        'client_phone' => 'nullable|string',
        'client_ruc' => 'nullable|string',
        'client_dni' => 'nullable|string',
    ]);

    // Buscar al cliente y actualizar sus datos
    $cliente = Cliente::findOrFail($id);
    $cliente->nombre = $request->client_name;
    $cliente->direccion = $request->client_address;
    $cliente->telefono = $request->client_phone; // Asegúrate de que se está guardando el teléfono
    $cliente->ruc = $request->client_ruc;
    $cliente->dni = $request->client_dni;
    $cliente->save(); // Guardar los cambios en la base de datos

    return response()->json(['success' => 'Cliente actualizado correctamente']);
}

public function store(Request $request)
{
    // Validar los datos del cliente
    $request->validate([
        'client_name' => 'required|string|max:255',
        'client_address' => 'nullable|string|max:255',
        'client_phone' => 'nullable|string|max:15',
        'client_dni' => 'nullable|string|max:20',
        'client_ruc' => 'nullable|string|max:20',
    ]);

    // Crear el cliente en la base de datos
    $cliente = new Cliente();
    $cliente->nombre = $request->client_name;
    $cliente->direccion = $request->client_address;
    $cliente->telefono = $request->client_phone;
    $cliente->dni = $request->client_dni;
    $cliente->ruc = $request->client_ruc;
    $cliente->save();

    // Devolver los datos del cliente guardado
    return response()->json($cliente);
}

}
