<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\DetalleLoteVenta;
use App\Models\LoteMovimiento;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'numero_lote',
        'codigo_comprobante',
        'fecha_ingreso',
        'fecha_vencimiento',
        'stock_inicial',
        'stock_actual',
        'precio_compra',
        'precio_unidad',
        'precio_paquete',
        'precio_caja',
        'activo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

public function proveedor()
{
    return $this->belongsTo(Proveedor::class);
}

public function ventas()
{
    return $this->hasMany(DetalleLoteVenta::class);
}
public function movimientos()
    {
        return $this->hasMany(LoteMovimiento::class, 'lote_id')
                    ->orderBy('creado_en', 'desc');
    }

}
