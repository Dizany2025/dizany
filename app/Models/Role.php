<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Role extends Model
{
    protected $table = 'roles'; // Tu tabla en la base de datos

    public $timestamps = false; // Desactívalo si no usas created_at/updated_at

    protected $fillable = ['nombre']; // Campos que puedes asignar masivamente

    // Relación: un rol tiene muchos usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
