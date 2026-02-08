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
use App\Models\Lote;


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
            'estado'           => 'pendiente',

            'estado_sunat'     => 'pendiente',
            'op_gravadas'      => 0,
            'igv'              => 0,
            'total'            => 0,
            'saldo'            => 0,
            'activo'           => 1
        ]);

        /* ================= DETALLE + STOCK (POR LOTES) ================= */
        $opGravadas = 0;

        foreach ($request->productos as $item) {

            // 1️⃣ seguridad básica
            $productoId = $item['producto_id'] ?? null;
            if (!$productoId) {
                throw new \Exception("Producto inválido en el carrito.");
            }

            $producto = Producto::findOrFail($productoId);

            // 2️⃣ datos que VIENEN DEL FRONTEND
            $presentacion = (string) ($item['presentacion'] ?? 'unidad');
            $unidadesAfectadas = (int) ($item['unidades'] ?? 0);
            $loteId = $item['lote_id'] ?? null;

            if ($unidadesAfectadas <= 0) {
                throw new \Exception("Cantidad inválida para {$producto->nombre}");
            }

            // 3️⃣ elegir lote (RESPETA frontend)
            $loteQuery = Lote::where('producto_id', $producto->id)
                ->where('stock_actual', '>', 0)
                ->orderByRaw('fecha_vencimiento IS NULL')
                ->orderBy('fecha_vencimiento', 'asc')
                ->orderBy('fecha_ingreso', 'asc')
                ->orderBy('id', 'asc');

            $lote = $loteId
                ? $loteQuery->where('id', $loteId)->lockForUpdate()->first()
                : $loteQuery->lockForUpdate()->first();

            if (!$lote) {
                throw new \Exception("No hay stock disponible por lotes para {$producto->nombre}");
            }

            // 4️⃣ validar stock SOLO DEL LOTE
            if ((int) $lote->stock_actual < $unidadesAfectadas) {
                throw new \Exception(
                    "Stock insuficiente en el lote {$lote->codigo_lote}. Quedan {$lote->stock_actual} und."
                );
            }

            // 5️⃣ precio según presentación (esto SÍ se conserva)
            $precioPresentacion = match ($presentacion) {
                'unidad'  => (float) ($lote->precio_unidad ?? 0),
                'paquete' => (float) ($lote->precio_paquete ?? 0),
                'caja'    => (float) ($lote->precio_caja ?? 0),
                default   => (float) ($lote->precio_unidad ?? 0),
            };

            if ($precioPresentacion <= 0) {
                throw new \Exception("No hay precio definido para {$producto->nombre}");
            }

            // 6️⃣ subtotal y ganancia
            $subtotal = round($precioPresentacion, 2);
            $opGravadas += $subtotal;

            $costoUnit  = (float) ($lote->precio_compra ?? 0);
            $costoTotal = round($costoUnit * $unidadesAfectadas, 2);
            $ganancia   = round($subtotal - $costoTotal, 2);

            // 7️⃣ detalle venta
            $detalle = DetalleVenta::create([
                'venta_id'            => $venta->id,
                'producto_id'         => $producto->id,
                'presentacion'        => $presentacion,
                'cantidad'            => 1, // 👈 ya no importa, es solo visual
                'unidades_afectadas'  => $unidadesAfectadas,
                'precio_presentacion' => $precioPresentacion,
                'precio_unitario'     => round($precioPresentacion / max($unidadesAfectadas, 1), 4),
                'subtotal'            => $subtotal,
                'ganancia'            => $ganancia,
                'activo'              => 1
            ]);

            // 8️⃣ descontar SOLO del lote elegido
            $lote->stock_actual -= $unidadesAfectadas;
            $lote->save();

            DB::table('detalle_lote_ventas')->insert([
                'detalle_venta_id' => $detalle->id,
                'lote_id'          => $lote->id,
                'cantidad'         => $unidadesAfectadas,
                'precio_lote'      => $precioPresentacion,
                'fecha_vencimiento'=> $lote->fecha_vencimiento,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }


        /* ================= IGV + TOTAL ================= */
        $opGravadas = round($opGravadas, 2);
        $igvMonto = round($opGravadas * ($igvPercent / 100), 2);
        $total    = round($opGravadas + $igvMonto, 2);

        /* ================= PAGO / ESTADO ================= */
        $montoPagado = round((float) $request->monto_pagado, 2);

        if ($montoPagado > 0 && empty($request->metodo_pago)) {
            throw new \Exception("Debe seleccionar un método de pago.");
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
        $formato = $request->input('formato', 'a4');
        $vista = match ($formato) {
            'ticket' => "comprobantes.{$tipo}_ticket",
            default  => "comprobantes.{$tipo}_a4",
        };

        if (!view()->exists($vista)) {
            throw new \Exception("La vista [$vista] no existe.");
        }

        $venta->load(['cliente', 'detalleVentas.producto']);

        // LOGO
        $logoBase64 = null;
        if ($config && $config->logo && file_exists(public_path($config->logo))) {
            $path = public_path($config->logo);
            $logoBase64 = 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) .
                ';base64,' . base64_encode(file_get_contents($path));
        }

        // QR (evita usar $venta->hash si no existe)
        $qrData = "{$config->ruc}|{$tipo}|{$serie}|{$correlativo}|{$venta->total}|{$venta->igv}|{$venta->fecha->format('d/m/Y')}";
        $qr = base64_encode(\QrCode::format('png')->size(120)->generate($qrData));

        $pdf = \PDF::setOptions([
            'isRemoteEnabled'   => true,
            'dpi'               => 96,
            'defaultMediaType'  => 'screen',
        ])->loadView($vista, [
            'venta' => $venta,
            'config' => $config,
            'qr' => $qr,
            'logoBase64' => $logoBase64,
            'subtotal' => $venta->op_gravadas,
            'igv' => $venta->igv,
            'total' => $venta->total,
        ]);

        if ($formato === 'ticket') {
            $alto = max(400, count($venta->detalleVentas) * 35 + 400);
            $pdf->setPaper([0, 0, 226.77, $alto]);
        } else {
            $pdf->setPaper('A4');
        }

        $nombreArchivo = "{$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT) . ".pdf";
        $ruta = public_path("comprobantes");
        if (!is_dir($ruta)) mkdir($ruta, 0775, true);

        $pdf->save("$ruta/$nombreArchivo");
        $pdfUrl = asset("comprobantes/$nombreArchivo");
        $venta->update(['pdf_url' => $pdfUrl]);

        /* ================= MOVIMIENTOS ================= */
        if ($estado === 'pagado') {
            Movimiento::create([
                'fecha' => $fechaHora->toDateString(),
                'tipo'  => 'ingreso',
                'subtipo' => 'venta',
                'concepto' => "Venta {$tipo} {$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT),
                'monto' => $total,
                'metodo_pago' => $metodoPagoVenta,
                'estado' => 'pagado',
                'referencia_id' => $venta->id,
                'referencia_tipo' => 'venta',
            ]);
        } elseif ($estado === 'pendiente') {
            Movimiento::create([
                'fecha' => $fechaHora->toDateString(),
                'tipo' => 'ingreso',
                'subtipo' => 'venta',
                'concepto' => "Venta pendiente {$tipo} {$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT),
                'monto' => $total,
                'metodo_pago' => 'fiado',
                'estado' => 'pendiente',
                'referencia_id' => $venta->id,
                'referencia_tipo' => 'venta',
            ]);
        } elseif ($estado === 'credito') {

            if ($montoPagado > 0) {
                Movimiento::create([
                    'fecha' => $fechaHora->toDateString(),
                    'tipo' => 'ingreso',
                    'subtipo' => 'venta',
                    'concepto' => "Adelanto venta {$tipo} {$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT),
                    'monto' => $montoPagado,
                    'metodo_pago' => $metodoPagoVenta,
                    'estado' => 'pagado',
                    'referencia_id' => $venta->id,
                    'referencia_tipo' => 'venta',
                ]);
            }

            Movimiento::create([
                'fecha' => $fechaHora->toDateString(),
                'tipo' => 'ingreso',
                'subtipo' => 'venta',
                'concepto' => "Saldo venta {$tipo} {$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT),
                'monto' => $saldo,
                'metodo_pago' => 'credito',
                'estado' => 'pendiente',
                'referencia_id' => $venta->id,
                'referencia_tipo' => 'venta',
            ]);
        }

        DB::commit();

        return response()->json([
            'success'        => true,
            'message'        => 'Venta registrada correctamente.',
            'serie'          => $serie,
            'correlativo'    => str_pad($correlativo, 6, '0', STR_PAD_LEFT),
            'pdf_url'        => $pdfUrl,
            'nombre_archivo' => $nombreArchivo,
            'estado'         => $estado,
            'saldo'          => $saldo,
            'monto_pagado'   => $montoPagado,
            'vuelto'         => $vuelto,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error registrarVenta: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


// VentaController.php
public function detalle($id)
{
    $venta = Venta::with(['cliente', 'detalleVentas.producto'])
        ->findOrFail($id);

    return response()->json([
        'id' => $venta->id,

        // texto superior
        'tipo' => ucfirst($venta->tipo_comprobante),

        // estado real
        'estado' => $venta->estado, // pagado | pendiente | credito

        // montos
        'total' => $venta->total,
        'saldo' => $venta->saldo, // 👈 CLAVE PARA EL SALDO PENDIENTE

        // fecha formateada (esto arregla el null)
        'fecha_formato' => $venta->fecha
            ? $venta->fecha->format('h:i A | d F Y')
            : null,

        // método
        'metodo_pago' => $venta->metodo_pago,

        // cliente COMO STRING (no objeto)
        'cliente'       => optional($venta->cliente)->razon_social
                                ?? optional($venta->cliente)->nombre
                                ?? optional($venta->cliente)->nombres
                                ?? '—',

        // ganancia total
        'ganancia' => $venta->detalleVentas->sum('ganancia'),

        // productos
        'productos' => $venta->detalleVentas->map(function ($d) {
            return [
                'nombre' => $d->producto->nombre,
                'descripcion' => $d->producto->descripcion,
                'imagen' => $d->producto->imagen
                    ? asset('uploads/productos/' . $d->producto->imagen)
                    : asset('img/sin-imagen.png'),
                'cantidad_txt' => "{$d->cantidad} {$d->presentacion}",
                'subtotal' => $d->subtotal,
            ];
        }),
    ]);
}
public function pagarCredito(Request $request, Venta $venta)
{
    $request->validate([
        'monto_pagado' => 'required|numeric|min:0.01',
        'metodo_pago'  => 'required|string',
    ]);

    if ($venta->estado !== 'credito') {
        return response()->json([
            'success' => false,
            'message' => 'La venta no está en crédito'
        ], 400);
    }

    $monto = round($request->monto_pagado, 2);

    // 🔥 Si paga menos → NO permitido
    if ($monto < $venta->saldo) {
        return response()->json([
            'success' => false,
            'message' => 'Debe pagar al menos el saldo pendiente'
        ], 400);
    }

    // 🔥 Si paga más → se ajusta (vuelto solo visual)
    if ($monto > $venta->saldo) {
        $monto = $venta->saldo;
    }

    DB::beginTransaction();

    try {
        // Registrar pago
        PagoVenta::create([
            'venta_id'    => $venta->id,
            'usuario_id'  => auth()->id(),
            'monto'       => $monto,
            'metodo_pago' => $request->metodo_pago,
        ]);

        // Actualizar saldo
        $nuevoSaldo = round($venta->saldo - $monto, 2);

        $venta->update([
            'saldo'  => $nuevoSaldo,
            'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'credito',
        ]);

        // Movimiento de ingreso
        Movimiento::create([
            'fecha' => now()->toDateString(),
            'tipo'  => 'ingreso',
            'subtipo' => 'cobro_credito',
            'concepto' => "Cobro crédito venta {$venta->serie}-" . str_pad($venta->correlativo, 6, '0', STR_PAD_LEFT),
            'monto' => $monto,
            'metodo_pago' => $request->metodo_pago,
            'estado' => 'pagado',
            'referencia_id' => $venta->id,
            'referencia_tipo' => 'venta',
        ]);
        Movimiento::where('referencia_id', $venta->id)
        ->where('subtipo', 'venta')
        ->where('estado', 'pendiente')
        ->where('metodo_pago', 'credito')
        ->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'saldo'   => $nuevoSaldo,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'successzsuccess' => false,
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



public function stockFifo($productoId)
{
    $lotes = Lote::where('producto_id', $productoId)
        ->where('stock_actual', '>', 0)
        ->orderByRaw('fecha_vencimiento IS NULL') // null al final
        ->orderBy('fecha_vencimiento', 'asc')     // FEFO real
        ->orderBy('fecha_ingreso', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    return response()->json(
        $lotes->map(fn($l) => [
            'id' => $l->id,
            'numero' => $l->id, // o $l->codigo_lote si tienes
            'stock' => (int) $l->stock_actual,     // 👈 OJO: stock en UNIDADES
            'precio_unidad' => (float) $l->precio_unidad,
            'precio_paquete' => (float) $l->precio_paquete,
            'precio_caja' => (float) $l->precio_caja,
            'fecha_vencimiento' => $l->fecha_vencimiento,
            'fecha_ingreso' => $l->fecha_ingreso,
        ])
    );
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
