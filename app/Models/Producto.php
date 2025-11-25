<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'codigo_barras',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'precio_mayor',
        'unidades_por_mayor',
        'stock',
        'ubicacion',
        'imagen',
        'fecha_vencimiento',
        'categoria_id',
        'marca_id',
        'activo', // ✅ agregado
    ];

    protected $casts = [
        'activo' => 'boolean', // ✅ interpreta como true/false
    ];

    public $timestamps = false;

    public function detalleVentas()
    {
        return $this->hasMany(\App\Models\DetalleVenta::class, 'producto_id');
    }
    // En el modelo Producto
public function categoria()
{
    return $this->belongsTo(Categoria::class);
}

public function marca()
{
    return $this->belongsTo(Marca::class);
}
// Si lo necesitas para el inventario: stock disponible
    public function tieneStock()
    {
        return $this->stock > 0;
    }

}
