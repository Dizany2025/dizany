<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'cantidad',
        'costo_unitario',
        'precio_venta',
        'fecha_ingreso',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso'     => 'date',
        'fecha_vencimiento' => 'date',
        'costo_unitario'    => 'decimal:2',
        'precio_venta'      => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function scopeActivos($q)
    {
        return $q->where('estado', 'activo');
    }

    public function scopeDisponibles($q)
    {
        return $q->activos()->where('cantidad', '>', 0);
    }

    public function scopeFefo($q)
    {
        // Si fecha_vencimiento es NULL, la mandamos al final
        return $q->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento ASC');
    }
}
