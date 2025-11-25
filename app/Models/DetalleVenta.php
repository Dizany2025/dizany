<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';
    public $timestamps = false;

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'tipo_venta',           // ← NUEVO
        'precio_unitario',
        'precio_mayor',
        'subtotal',
        'ganancia',
        'unidades_descuento'    // ← NUEVO
    ];

    /**
     * Relación: Un detalle de venta pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Relación: Un detalle de venta pertenece a un producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
