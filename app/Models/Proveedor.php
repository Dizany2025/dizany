<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'tipo_documento',
        'numero_documento',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'estado'
    ];
}

