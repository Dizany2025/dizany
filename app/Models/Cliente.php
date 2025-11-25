<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['dni', 'ruc', 'nombre', 'direccion', 'telefono'];

    public $timestamps = false; // 👈 Agrega esto.

    

}
