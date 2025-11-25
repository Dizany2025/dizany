<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marcas';  // Opcional, si quieres ser explícito

    protected $fillable = ['nombre', 'descripcion'];
    public $timestamps = false;

    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }

}
