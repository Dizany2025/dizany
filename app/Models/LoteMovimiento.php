<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteMovimiento extends Model
{
    use HasFactory;

    // Nombre real de la tabla
    protected $table = 'lote_movimientos';

    // La tabla no usa updated_at
    public $timestamps = false;

    // Campos que se pueden insertar de forma masiva
    protected $fillable = [
        'lote_id',
        'usuario_id',
        'tipo',           // ingreso | venta | ajuste | edicion
        'cantidad',
        'stock_antes',
        'stock_despues',
        'motivo',
        'creado_en',
    ];

    // Casts útiles
    protected $casts = [
        'creado_en' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    
}
