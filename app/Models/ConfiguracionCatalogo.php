<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionCatalogo extends Model
{
    protected $table = 'configuracion_catalogo';

    protected $fillable = [
        'nombre_empresa',
        'rubro',
        'logo',
        'telefono',
        'correo',
        'direccion',
        'color_principal',
        'mensaje_bienvenida',
        'texto_boton_whatsapp',
    ];
}
