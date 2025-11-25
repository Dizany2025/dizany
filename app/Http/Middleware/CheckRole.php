<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Maneja la petición entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
{
    $user = $request->user();

    // Depurar roles
    //dd('Rol del usuario:', $user->rol->nombre, 'Roles permitidos:', $roles);

    if (! $user || ! in_array($user->rol->nombre, $roles)) {
        abort(403, 'No tienes acceso a esta sección');
    }

    return $next($request);
}

}
