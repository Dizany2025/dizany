<?php
namespace App\Http\Controllers;


use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Venta;
use App\Models\User; 
use Carbon\Carbon;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Factura;
use App\Models\Configuracion;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Movimiento;
use App\Models\PagoVenta;


class VentaController extends Controller
{
    // Mostrar la interfaz para registrar una nueva venta
    public function index()
    {
        // Configuración (IGV, empresa, etc.)
        $config = Configuracion::first();

        // Categorías activas y ordenadas
        $categorias = \App\Models\Categoria::orderBy('nombre', 'ASC')->get();

        // Productos visibles disponibles
        $productos = Producto::where('activo', true)
            ->where('visible_en_catalogo', true)
            ->orderBy('nombre', 'ASC')
            ->get();

        return view('ventas.index', compact('config', 'categorias', 'productos'));
    }
public function filtrarPorCategoria(Request $request)
{
    $productos = Producto::where('categoria_id', $request->id)
        ->where('activo', true)
        ->where('visible_en_catalogo', true)
        ->orderBy('nombre', 'ASC')
        ->get();

    return response()->json($productos);
}

public function registrarVenta(Request $request)
{
    $request->validate([
        'tipo_comprobante' => 'required|string',
        'documento'        => 'required|string',
        'fecha'            => 'required|date',
        'hora'             => 'required',
        'productos'        => 'required|array|min:1',

        'monto_pagado'     => 'required|numeric|min:0',
        'metodo_pago'      => 'nullable|string',
        'formato'          => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        /* ================= CLIENTE ================= */
        $cliente = Cliente::where('ruc', $request->documento)
            ->orWhere('dni', $request->documento)
            ->firstOrFail();

        /* ================= FECHA ================= */
        $hora = strlen($request->hora) === 5 ? $request->hora . ':00' : $request->hora;
        $fechaHora = Carbon::createFromFormat('Y-m-d H:i:s', "{$request->fecha} {$hora}");

        /* ================= SERIE ================= */
        $tipo = $request->tipo_comprobante;
        $serie = match ($tipo) {
            'boleta'  => 'B001',
            'factura' => 'F001',
            default   => 'NV01',
        };

        $correlativo = (int) (Venta::where('serie', $serie)->max('correlativo') ?? 0) + 1;

        /* ================= CONFIG ================= */
        $config = Configuracion::first();
        $igvPercent = $config->igv ?? 0;

        /* ================= VENTA BASE ================= */
        $venta = Venta::create([
            'cliente_id'       => $cliente->id,
            'usuario_id'       => auth()->id(),
            'fecha'            => $fechaHora,
            'tipo_comprobante' => $tipo,
            'serie'            => $serie,
            'correlativo'      => $correlativo,

            'metodo_pago'      => null,
            'estado'           => 'pendiente', // 🔧 FIX: siempre minúscula

            'estado_sunat'     => 'pendiente',
            'op_gravadas'      => 0,
            'igv'              => 0,
            'total'            => 0,
            'saldo'            => 0,
            'activo'           => 1
        ]);

        /* ================= DETALLE + STOCK ================= */
        $opGravadas = 0;

        foreach ($request->productos as $item) {

            $producto = Producto::findOrFail($item['producto_id']);

            $cantidad     = (int) $item['cantidad'];
            $presentacion = $item['presentacion'];

            $uPaquete = $producto->unidades_por_paquete ?? 1;
            $pCaja    = $producto->paquetes_por_caja ?? 1;

            $unidadesAfectadas = match ($presentacion) {
                'unidad'  => $cantidad,
                'paquete' => $cantidad * $uPaquete,
                'caja'    => $cantidad * $uPaquete * $pCaja,
                default   => $cantidad
            };

            if ($producto->stock < $unidadesAfectadas) {
                throw new Exception("Stock insuficiente para {$producto->nombre}");
            }

            $precioPresentacion = match ($presentacion) {
                'unidad'  => $producto->precio_venta,
                'paquete' => $producto->precio_paquete,
                'caja'    => $producto->precio_caja,
                default   => $producto->precio_venta
            };

            $subtotal = $precioPresentacion * $cantidad;
            $opGravadas += $subtotal;

            $costoTotal = $producto->precio_compra * $unidadesAfectadas;
            $ganancia   = $subtotal - $costoTotal;

            DetalleVenta::create([
                'venta_id'           => $venta->id,
                'producto_id'        => $producto->id,
                'presentacion'       => $presentacion,
                'cantidad'           => $cantidad,
                'unidades_afectadas' => $unidadesAfectadas,
                'precio_presentacion'=> $precioPresentacion,
                'precio_unitario'    => round($precioPresentacion / max($unidadesAfectadas, 1), 4),
                'subtotal'           => $subtotal,
                'ganancia'           => $ganancia,
                'activo'             => 1
            ]);

            $producto->decrement('stock', $unidadesAfectadas);
        }

        /* ================= IGV + TOTAL ================= */
        $igvMonto = round($opGravadas * ($igvPercent / 100), 2);
        $total    = round($opGravadas + $igvMonto, 2);
        $opGravadas = round($opGravadas, 2);

        /* ================= PAGO / ESTADO ================= */
        $montoPagado = round((float) $request->monto_pagado, 2);

        if ($montoPagado > 0 && empty($request->metodo_pago)) {
            throw new Exception("Debe seleccionar un método de pago.");
        }

        $vuelto = 0;
        if ($montoPagado > $total) {
            $vuelto = round($montoPagado - $total, 2);
            $montoPagado = $total;
        }

        if ($montoPagado <= 0) {
            $estado = 'pendiente';
            $saldo  = $total;
            $metodoPagoVenta = null;
        } elseif ($montoPagado < $total) {
            $estado = 'credito';
            $saldo  = round($total - $montoPagado, 2);
            $metodoPagoVenta = $request->metodo_pago;
        } else {
            $estado = 'pagado';
            $saldo  = 0;
            $metodoPagoVenta = $request->metodo_pago;
        }

        $venta->update([
            'op_gravadas' => $opGravadas,
            'igv'         => $igvMonto,
            'total'       => $total,
            'saldo'       => $saldo,
            'estado'      => $estado,
            'metodo_pago' => $metodoPagoVenta,
        ]);

        if ($montoPagado > 0) {
            PagoVenta::create([
                'venta_id'    => $venta->id,
                'usuario_id'  => auth()->id(),
                'monto'       => $montoPagado,
                'metodo_pago' => $request->metodo_pago,
            ]);
        }

        // ============================== GENERAR PDF ==============================
        $formato = $request->input('formato', 'a4'); $vista = match ($formato) 
        { 'ticket' => "comprobantes.{$tipo}_ticket", default => 
        "comprobantes.{$tipo}_a4", }; if (!view()->exists($vista)) { throw new \Exception("La vista [$vista] no existe."); } $venta->load(['cliente', 'detalleVentas.producto']); 
        // LOGO
        $logoBase64 = null; if ($config && $config->logo && file_exists(public_path($config->logo)))
        { $path = public_path($config->logo); $logoBase64 = 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) .
         ';base64,' . base64_encode(file_get_contents($path)); }
         // QR 
        $qrData = "{$config->ruc}|{$tipo}|{$serie}|{$correlativo}|{$venta->total}|{$venta->igv}|{$venta->fecha->format('d/m/Y')}|{$venta->hash}";
         $qr = base64_encode(\QrCode::format('png')->size(120)->generate($qrData)); $pdf = \PDF::setOptions([ 'isRemoteEnabled' => true, 'dpi' => 96, 'defaultMediaType' => 'screen', ])->loadView($vista, [ 'venta' => $venta, 'config' => $config, 'qr' => $qr, 'logoBase64' => $logoBase64, 'subtotal' => $venta->op_gravadas, 'igv' => $venta->igv, 'total' => $venta->total, ]); if ($formato === 'ticket') { $alto = max(400, count($venta->detalleVentas) * 35 + 400); $pdf->setPaper([0, 0, 226.77, $alto]); } else { $pdf->setPaper('A4'); } $nombreArchivo = "{$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT) . ".pdf"; $ruta = public_path("comprobantes"); if (!is_dir($ruta)) mkdir($ruta, 0775, true); $pdf->save("$ruta/$nombreArchivo"); $pdfUrl = asset("comprobantes/$nombreArchivo"); $venta->update(['pdf_url' => $pdfUrl]); 

        /* ================= MOVIMIENTO ================= */
        Movimiento::create([
            'fecha'           => $fechaHora->toDateString(),
            'tipo'            => 'ingreso',
            'subtipo'         => 'venta',
            'concepto'        => "Venta {$tipo} {$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT),
            'monto'           => $total,

            // ✅ FIX DEFINITIVO
            'metodo_pago'     => in_array($estado, ['pendiente', 'credito'])
                ? 'CREDITO'
                : $metodoPagoVenta,

            'estado'          => $saldo <= 0 ? 'pagado' : 'pendiente',
            'referencia_id'   => $venta->id,
            'referencia_tipo' => 'venta',
        ]);
        
        DB::commit();

        return response()->json([
    'success'        => true,
    'message'        => 'Venta registrada correctamente.',
    'serie'          => $serie,
    'correlativo'    => str_pad($correlativo, 6, '0', STR_PAD_LEFT),
    'pdf_url'        => $pdfUrl,
    'nombre_archivo' => $nombreArchivo,

    // info extra que ya usas
    'estado'         => $estado,
    'saldo'          => $saldo,
    'monto_pagado'   => $montoPagado,
    'vuelto'         => $vuelto,
]);


    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error registrarVenta: ".$e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function cerrarPendiente(Request $request, Venta $venta)
{
    $request->validate([
        'monto_pagado' => 'required|numeric|min:0.01',
        'metodo_pago'  => 'required|string',
    ]);

    // 🔴 USAR ESTADO REAL
    if ($venta->estado !== 'pendiente') {
        return response()->json([
            'success' => false,
            'message' => 'La venta no está pendiente'
        ], 400);
    }

    $total = (float) $venta->total;
    $monto = (float) $request->monto_pagado;

    if ($monto < $total) {
        return response()->json([
            'success' => false,
            'message' => 'El monto recibido no puede ser menor al total'
        ], 400);
    }

    DB::transaction(function () use ($venta, $request, $total) {

        // 1️⃣ Registrar pago
        PagoVenta::create([
            'venta_id'    => $venta->id,
            'usuario_id'  => auth()->id(),
            'monto'       => $total,
            'metodo_pago' => $request->metodo_pago,
        ]);

        // 2️⃣ Cerrar venta (CAMPO CORRECTO)
        $venta->update([
            'estado'      => 'pagado',
            'saldo'       => 0,
            'metodo_pago' => $request->metodo_pago,
        ]);

        // 3️⃣ ACTUALIZAR MOVIMIENTO (ESTO FALTABA)
        Movimiento::where('referencia_tipo', 'venta')
            ->where('referencia_id', $venta->id)
            ->update([
                'estado'      => 'pagado',
                'metodo_pago' => $request->metodo_pago,
            ]);
    });

    return response()->json([
        'success' => true,
        'vuelto'  => round($monto - $total, 2),
    ]);
}



public function obtenerSerieCorrelativo(Request $request)
{
    $tipo = $request->query('tipo');

    $serie = match ($tipo) {
        'boleta' => 'B001',
        'factura' => 'F001',
        'nota_venta' => 'NV01',
        default => 'ND00',
    };

    $ultimoCorrelativo = DB::table('ventas')
        ->where('tipo_comprobante', $tipo)
        ->where('serie', $serie)
        ->max('correlativo');

    $nuevoCorrelativo = $ultimoCorrelativo ? $ultimoCorrelativo + 1 : 1;
    $correlativoFormateado = str_pad($nuevoCorrelativo, 6, '0', STR_PAD_LEFT);

    return response()->json([
        'serie' => $serie,
        'correlativo' => $correlativoFormateado,
    ]);
}

public function show($id)
{
    $venta = Venta::with(['cliente', 'detalleVentas.producto'])->findOrFail($id);

    // ================= SALDO SEGURO =================
    $saldo = $venta->estado === 'credito'
        ? (float) ($venta->saldo ?? 0)
        : 0;

    return response()->json([
        'id'            => $venta->id,
        'cliente'       => $venta->cliente->nombre ?? '—',
        'tipo'          => $venta->tipo_comprobante,
        'serie'         => $venta->serie,
        'correlativo'   => $venta->correlativo,
        'estado'        => $venta->estado,
        'total'         => (float) $venta->total,
        'saldo'         => $saldo, // 🔥 CLAVE
        'metodo_pago'   => $venta->metodo_pago
                                ? ucfirst($venta->metodo_pago)
                                : null,
        'fecha_formato' => $venta->fecha
                                ? Carbon::parse($venta->fecha)->format('h:i A | d F Y')
                                : '—',
        'ganancia'      => (float) $venta->detalleVentas->sum('ganancia'),

        'productos' => $venta->detalleVentas->map(function ($item) {

            $cantidadTxt = match ($item->presentacion) {
                'caja'    => $item->cantidad . ' caja x' . $item->unidades_afectadas,
                'paquete' => $item->cantidad . ' paquete x' . $item->unidades_afectadas,
                default   => $item->cantidad . ' unidad'
            };

            return [
                'nombre'        => $item->producto->nombre,
                'descripcion'   => $item->producto->descripcion ?? '',
                'imagen'        => $item->producto->imagen
                    ? asset('uploads/productos/' . basename($item->producto->imagen))
                    : asset('images/producto-default.png'),
                'cantidad_txt'  => $cantidadTxt,
                'subtotal'      => (float) $item->subtotal,
            ];
        }),
    ]);
}

public function pagarCredito(Request $request, $id)
{
    $request->validate([
        'monto'        => 'required|numeric|min:0.01',
        'metodo_pago'  => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        $venta = Venta::lockForUpdate()->findOrFail($id);

        if ($venta->estado !== 'credito') {
            throw new \Exception('La venta no es a crédito');
        }

        if ($request->monto > $venta->saldo) {
            throw new \Exception('El monto supera el saldo pendiente');
        }

        PagoVenta::create([
            'venta_id'   => $venta->id,
            'usuario_id' => auth()->id(),
            'monto'      => $request->monto,
            'metodo_pago'=> $request->metodo_pago,
            'fecha_pago' => now(),
        ]);

        $nuevoSaldo = $venta->saldo - $request->monto;

        $venta->update([
            'saldo'  => $nuevoSaldo,
            'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'credito',
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'saldo'   => $nuevoSaldo,
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}


    public function destroy($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID de venta inválido'], 400);
        }

        try {
            DB::transaction(function () use ($id) {
                $venta = Venta::with('detalleVentas.producto')->findOrFail($id);

                foreach ($venta->detalleVentas as $detalle) {
                    $detalle->producto->increment('stock', $detalle->cantidad);
                }

                $venta->detalleVentas()->delete();
                $venta->delete();
            });

            return response()->json(['success' => true, 'message' => 'Venta eliminada y stock reintegrado']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Venta $venta)
{
    $venta->load(['cliente', 'detalleVentas.producto']);
    $clientes = Cliente::all();
    $productos = Producto::where('stock', '>', 0)->get();
    $config = Configuracion::first();

    return view('ventas.edit', compact('venta', 'clientes', 'productos', 'config'));
}


// ACTUALIZAR VENTA Y GANANCIAS
public function update(Request $request, Venta $venta)
{
    $request->validate([
        'cliente_id'                      => 'required|exists:clientes,id',
        'fecha'                           => 'required|date',
        'metodo_pago'                     => 'required|string',
        'productos'                       => 'required|array|min:1',
        'productos.*.cantidad'           => 'required|numeric|min:1',
        'productos.*.precio_mayor'       => 'nullable|numeric|min:0',
        'productos.*.tipo_venta'         => 'required|in:unidad,mayor',
    ]);

    try {
        DB::beginTransaction();

        // Restaurar stock anterior
        $detallesAnteriores = $venta->detalleVentas()->get();
        foreach ($detallesAnteriores as $detalle) {
            $producto = Producto::find($detalle->producto_id);
            if ($producto) {
                $producto->stock += $detalle->unidades_descuento ?? $detalle->cantidad; // Por compatibilidad
                $producto->save();
            }
        }

        // Actualizar datos de la venta
        $venta->update([
            'cliente_id'  => $request->cliente_id,
            'fecha'       => $request->fecha,
            'metodo_pago' => $request->metodo_pago,
        ]);

        // Eliminar los detalles anteriores
        $venta->detalleVentas()->delete();

        $total = 0;

        foreach ($request->productos as $productoId => $datos) {
            $producto = Producto::findOrFail($productoId);
            $cantidad = intval($datos['cantidad']);
            $tipoVenta = $datos['tipo_venta'];
            $precioUnitario = floatval($producto->precio_venta);
            $precioMayor = floatval($datos['precio_mayor'] ?? 0);
            $unidadesPorMayor = intval($producto->unidades_por_mayor) ?: 1;

            // Calcular precio aplicado y unidades reales
            $precioAplicado = ($tipoVenta === 'mayor' && $precioMayor > 0) ? $precioMayor : $precioUnitario;
            $unidadesDescuento = $tipoVenta === 'mayor' ? $cantidad * $unidadesPorMayor : $cantidad;

            // Calcular ganancia según tipo
            if ($tipoVenta === 'mayor') {
                $costoCaja = $producto->precio_compra * $unidadesPorMayor;
                $ganancia = ($precioMayor - $costoCaja) * $cantidad;
            } else {
                $ganancia = ($precioUnitario - $producto->precio_compra) * $cantidad;
            }

            $subtotal = $precioAplicado * $cantidad;
            $total += $subtotal;

            DetalleVenta::create([
                'venta_id'          => $venta->id,
                'producto_id'       => $productoId,
                'cantidad'          => $cantidad,
                'tipo_venta'        => $tipoVenta,
                'precio_unitario'   => $precioUnitario,
                'precio_mayor'      => $precioMayor > 0 ? $precioMayor : null,
                'subtotal'          => $subtotal,
                'ganancia'          => $ganancia,
                'unidades_descuento'=> $unidadesDescuento
            ]);

            // Actualizar stock
            $producto->stock -= $unidadesDescuento;
            $producto->save();
        }

        $venta->total = $total;
        $venta->save();

        DB::commit();

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Venta actualizada correctamente.'])
            : redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        $msg = 'Ocurrió un error: ' . $e->getMessage();
        return $request->ajax()
            ? response()->json(['success' => false, 'message' => $msg])
            : back()->with('error', $msg);
    }
}

//EXPORTAR EXCEL & PDF
 public function exportarExcel(Request $request)
{
    return Excel::download(new VentasExport($request), 'ventas_filtradas.xlsx');
}

public function exportarPDF(Request $request)
{
    $rango = $request->input('filter-type', 'diario');
    $fecha = $request->input('filter-date', Carbon::today()->toDateString());
    $usuarioId = $request->input('filter-user', null);
    $cliente = $request->input('filter-client', null);

    $ventas = Venta::with(['cliente', 'usuario', 'detalleVentas']);

    if ($rango === 'diario') {
        $ventas->whereDate('fecha', Carbon::parse($fecha));
    } elseif ($rango === 'semanal') {
        $start = Carbon::parse($fecha)->startOfWeek();
        $end = Carbon::parse($fecha)->endOfWeek();
        $ventas->whereBetween('fecha', [$start, $end]);
    } elseif ($rango === 'mensual') {
        $ventas->whereMonth('fecha', Carbon::parse($fecha)->month)
               ->whereYear('fecha', Carbon::parse($fecha)->year);
    }

    if ($usuarioId) {
        $ventas->where('usuario_id', $usuarioId);
    }

    if ($cliente) {
        $ventas->whereHas('cliente', function ($query) use ($cliente) {
            $query->where('nombre', 'LIKE', "%{$cliente}%")
                  ->orWhere('dni', 'LIKE', "%{$cliente}%")
                  ->orWhere('ruc', 'LIKE', "%{$cliente}%");
        });
    }

    $ventas = $ventas->orderByDesc('fecha')->get();

    $pdf = PDF::loadView('exports.ventas_pdf', compact('ventas'));
    return $pdf->download('ventas_filtradas.pdf');
}
public function autorizar(Request $request)
{
    $usuario = $request->input('usuario');
    $clave = $request->input('clave');

    $user = User::where('usuario', $usuario)
                ->with('rol') // Cargar relación
                ->first();

    if ($user) {
        \Log::info('Usuario encontrado: ' . $user->nombre);
        \Log::info('Rol del usuario: ' . ($user->rol->nombre ?? 'No definido'));
        \Log::info('Clave hash en BD: ' . $user->clave);

        // ⚠️ Verificar si el rol no es ADMINISTRADOR (por rol_id)
        if ($user->rol_id != 1) {
            \Log::warning('⛔ Usuario no autorizado. No es administrador.');
            return response()->json([
                'success' => false,
                'message' => 'USUARIO NO AUTORIZADO (NO TIENES PERMISO DE ADMINISTRADOR PARA EDITAR ESTA VENTA)'
            ], 401);
        }

        // Verificar contraseña
        if (Hash::check($clave, $user->clave)) {
            \Log::info('✅ Clave correcta');
            return response()->json(['success' => true]);
        } else {
            \Log::warning('❌ Clave incorrecta');
        }
    } else {
        \Log::warning('❌ Usuario no encontrado');
    }

    return response()->json(['success' => false], 401);
}

public function descargarComprobante($filename)
{
    $path = public_path("comprobantes/{$filename}");

    if (!file_exists($path)) {
        abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename, [
        'Content-Type' => 'application/pdf'
    ]);
}


public function imprimirFactura($id)
{
    $venta = Venta::with('cliente', 'detalleVentas.producto')->findOrFail($id);
    $config = Configuracion::first();

    // Texto para el QR (puede incluir RUC, serie, correlativo, total, etc.)
    $textoQR = "{$config->ruc}|{$venta->serie}-{$venta->correlativo}|{$venta->total}|{$venta->fecha->format('d/m/Y')}";

    // Generamos QR como imagen en Base64
    $qr = base64_encode(QrCode::format('png')->size(120)->generate($textoQR));

    return view('factura', compact('venta', 'config', 'qr'));
}

}
