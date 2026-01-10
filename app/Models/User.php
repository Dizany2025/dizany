<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios'; // Nombre de tu tabla

    protected $primaryKey = 'id';

    public $timestamps = false; // Desactiva si tu tabla no tiene created_at/updated_at

    protected $fillable = [
        'nombre',
        'usuario',
        'email', // ✅ agregado
        'clave',
        'rol_id',
    ];

    protected $hidden = [
        'clave',
    ];

    // Laravel usará 'clave' como campo de contraseña
    public function getAuthPassword()
    {
        return $this->clave;
    }

    // Relación con roles
    public function rol()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }
  //  public function getAuthIdentifierName()
//{
  //  return 'usuario';
//}
public function getEmailForPasswordReset()
{
    return $this->email;
}
public function esAdmin()
{
    return $this->rol_id == 1;
}

}
