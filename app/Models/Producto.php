<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Lote;


class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'codigo_barras',
        'nombre',
        'slug',                    // 🆕 nuevo
        'descripcion',
        'precio_compra',
        'precio_venta',

        // 🆕 nuevos campos correctos
        'precio_paquete',
        'unidades_por_paquete',
        'paquetes_por_caja',
        'tipo_paquete',
        'precio_caja',

        'stock',
        'ubicacion',
        'imagen',
        'fecha_vencimiento',
        'categoria_id',
        'marca_id',
        'activo',
        'visible_en_catalogo',      // 🆕 nuevo
    ];

    protected $casts = [
        'activo' => 'boolean',
        'visible_en_catalogo' => 'boolean',   // 🆕
        'fecha_vencimiento' => 'date',
    ];


    /* -------------------
       Relaciones
    --------------------*/
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function detalleVentas()
    {
        return $this->hasMany(\App\Models\DetalleVenta::class, 'producto_id');
    }

    /* -------------------
       Utilidades
    --------------------*/
    public function tieneStock()
    {
        return $this->stock > 0;
    }

    /* -------------------
       Slug automático
    --------------------*/
    protected static function booted()
    {
        static::creating(function ($producto) {
            if (empty($producto->slug)) {
                $producto->slug = Str::slug($producto->nombre);
            }
        });
    }

    public function lotes()
{
    return $this->hasMany(Lote::class);
}
}
