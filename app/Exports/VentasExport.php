<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VentasExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $rango = $this->request->input('filter-type', 'diario');
        $fecha = $this->request->input('filter-date', Carbon::today()->toDateString());
        $usuarioId = $this->request->input('filter-user', null);
        $cliente = $this->request->input('filter-client', null);

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

        return view('exports.ventas', compact('ventas'));
    }
}
