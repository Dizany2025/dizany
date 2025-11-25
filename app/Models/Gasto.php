<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención)
    protected $table = 'gastos';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'usuario_id',
        'descripcion',
        'monto',
        'fecha',
        'metodo_pago',
    ];

    // Relación con el usuario (opcional, si quieres usarlo)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    
}
