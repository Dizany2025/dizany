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
        'metodo_pago'      => 'required|string',
        'estado_pago'      => 'required|string',
        'productos'        => 'required|array|min:1',
    ]);

    DB::beginTransaction();

    try {
        // ================= CLIENTE =================
        $cliente = Cliente::where('ruc', $request->documento)
            ->orWhere('dni', $request->documento)
            ->firstOrFail();

        // ================= FECHA =================
        $hora = strlen($request->hora) === 5 ? $request->hora . ':00' : $request->hora;
        $fechaHora = Carbon::createFromFormat('Y-m-d H:i:s', "{$request->fecha} {$hora}");

        // ================= SERIE =================
        $tipo = $request->tipo_comprobante;
        $serie = match ($tipo) {
            'boleta' => 'B001',
            'factura' => 'F001',
            default => 'NV01',
        };

        $correlativo = Venta::where('serie', $serie)->max('correlativo') + 1;

        // ================= VENTA =================
        $venta = Venta::create([
            'cliente_id'       => $cliente->id,
            'usuario_id'       => auth()->id(),
            'fecha'            => $fechaHora,
            'tipo_comprobante' => $tipo,
            'serie'            => $serie,
            'correlativo'      => $correlativo,
            'metodo_pago'      => $request->metodo_pago,
            'estado'           => $request->estado_pago,
            'estado_sunat'     => 'pendiente',
            'op_gravadas'      => 0,
            'igv'              => 0,
            'total'            => 0,
            'activo'           => 1
        ]);

        // ================= CONFIG =================
        $config = Configuracion::first();
        $igvPercent = $config->igv ?? 0;

        $opGravadas = 0;

        // ================= DETALLE =================
        $totalBase = 0;

        foreach ($request->productos as $item) {

            $producto = Producto::findOrFail($item['producto_id']);

            $cantidad     = (int) $item['cantidad'];
            $presentacion = $item['presentacion']; // 🔥 AHORA SÍ

            $uPaquete = $producto->unidades_por_paquete ?? 1;
            $pCaja    = $producto->paquetes_por_caja ?? 1;

            // =========================
            // UNIDADES AFECTADAS
            // =========================
            $unidadesAfectadas = match ($presentacion) {
                'unidad'  => $cantidad,
                'paquete' => $cantidad * $uPaquete,
                'caja'    => $cantidad * $uPaquete * $pCaja,
                default   => $cantidad
            };

            if ($producto->stock < $unidadesAfectadas) {
                throw new Exception("Stock insuficiente para {$producto->nombre}");
            }

            // =========================
            // PRECIO POR PRESENTACIÓN
            // =========================
            $precioPresentacion = match ($presentacion) {
                'unidad'  => $producto->precio_venta,
                'paquete' => $producto->precio_paquete,
                'caja'    => $producto->precio_caja,
                default   => $producto->precio_venta
            };

            $subtotal = $precioPresentacion * $cantidad;

            $opGravadas += $subtotal;   // 🔥 ESTO FALTABA
            $totalBase += $subtotal;

            // =========================
            // GANANCIA REAL
            // =========================
            $costoUnitario = $producto->precio_compra;
            $costoTotal    = $costoUnitario * $unidadesAfectadas;
            $ganancia      = $subtotal - $costoTotal;

            DetalleVenta::create([
                'venta_id'            => $venta->id,
                'producto_id'         => $producto->id,
                'presentacion'        => $presentacion,
                'cantidad'            => $cantidad,
                'unidades_afectadas'  => $unidadesAfectadas,
                'precio_presentacion' => $precioPresentacion,
                'precio_unitario'     => round($precioPresentacion / $unidadesAfectadas, 4),
                'subtotal'            => $subtotal,
                'ganancia'            => $ganancia,
                'activo'              => 1
            ]);

            $producto->decrement('stock', $unidadesAfectadas);
        }

                // ================= IGV =================
                $igvMonto = $opGravadas * ($igvPercent / 100);
                $total = $opGravadas + $igvMonto;

                $venta->update([
                    'op_gravadas' => round($opGravadas, 2),
                    'igv'         => round($igvMonto, 2),
                    'total'       => round($total, 2),
                ]);

                // ==============================
                //   GENERAR PDF
                // ==============================
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

                // QR (hash puede ser null si no lo generas en otro lado)
                $qrData = "{$config->ruc}|{$tipo}|{$serie}|{$correlativo}|{$venta->total}|{$venta->igv}|{$venta->fecha->format('d/m/Y')}|{$venta->hash}";
                $qr = base64_encode(\QrCode::format('png')->size(120)->generate($qrData));

                $pdf = \PDF::setOptions([
                    'isRemoteEnabled'  => true,
                    'dpi'              => 96,
                    'defaultMediaType' => 'screen',
                ])->loadView($vista, [
                    'venta'      => $venta,
                    'config'     => $config,
                    'qr'         => $qr,
                    'logoBase64' => $logoBase64,
                    'subtotal'   => $venta->op_gravadas,
                    'igv'        => $venta->igv,
                    'total'      => $venta->total,
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
                // =========================
                // REGISTRAR MOVIMIENTO
                // =========================

                Movimiento::create([
                    'fecha'            => $fechaHora->toDateString(),
                    'tipo'             => 'ingreso',
                    'subtipo'          => 'venta',
                    'concepto'         => "Venta {$tipo} {$serie}-" . str_pad($correlativo, 6, '0', STR_PAD_LEFT),
                    'monto'            => $total,
                    'metodo_pago'      => $request->metodo_pago,
                    'estado'           => $request->estado_pago === 'pagado' ? 'pagado' : 'pendiente',
                    'referencia_id'    => $venta->id,
                    'referencia_tipo'  => 'venta',
                ]);


                DB::commit();

                return response()->json([
                'success'        => true,
                'message'        => 'Venta registrada correctamente.',
                'serie'          => $serie,
                'correlativo'    => str_pad($correlativo, 6, '0', STR_PAD_LEFT),
                'pdf_url'        => $pdfUrl,
                'nombre_archivo' => $nombreArchivo
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
    $venta = Venta::with(['cliente', 'usuario', 'detalleVentas.producto'])->findOrFail($id);

    // Usar el campo guardado "ganancia" directamente
    $ganancia = $venta->detalleVentas->sum('ganancia');

    return response()->json([
        'cliente'          => $venta->cliente->nombre ?? $venta->documento,
        'tipo_comprobante' => $venta->tipo_comprobante,
        'total'            => $venta->total,
        'fecha'            => Carbon::parse($venta->fecha)->format('d/m/Y H:i'),
        'usuario'          => $venta->usuario->nombre,
        'ganancia'         => $ganancia,
        'estado'           => $venta->estado,
        'productos'        => $venta->detalleVentas->map(function ($item) {
            return [
                'nombre'          => $item->producto->nombre,
                'cantidad'        => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'subtotal'        => $item->subtotal,
                'ganancia'        => $item->ganancia,
                'precio_compra'   => $item->producto->precio_compra,
            ];
        }),
    ]);
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
