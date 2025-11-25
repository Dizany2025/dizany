<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function dashboard()
    {
        // Obtener productos sin stock
        $productosSinStock = Producto::where('stock', 0)->get();

        // Obtener últimas ventas
        $ultimasVentas = Venta::with('cliente')->orderByDesc('fecha')->take(5)->get();

        // Datos de ventas semanales (últimos 7 días)
        $ventasSemana = Venta::select(DB::raw('DATE(fecha) as fecha'), DB::raw('SUM(total) as total'))
            ->whereBetween('fecha', [now()->subDays(6), now()])
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy('fecha')
            ->get();

        return view('admin.dashboard', compact('productosSinStock', 'ultimasVentas', 'ventasSemana'));
    }
}
