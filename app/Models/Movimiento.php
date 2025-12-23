<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'fecha',
        'hora',
        'tipo',
        'subtipo',
        'concepto',
        'monto',
        'metodo_pago',
        'estado',
        'referencia_id',
        'referencia_tipo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora'  => 'datetime:H:i:s',
        'monto' => 'decimal:2',
    ];

    /* =========================
       SCOPES (MUY ÚTILES)
    ========================= */

    public function scopeIngresos($query)
    {
        return $query->where('tipo', 'ingreso');
    }

    public function scopeEgresos($query)
    {
        return $query->where('tipo', 'egreso');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function venta()
    {
        return $this->belongsTo(\App\Models\Venta::class, 'referencia_id');
    }

}
