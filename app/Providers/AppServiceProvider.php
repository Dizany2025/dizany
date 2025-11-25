<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Configuracion;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
{
    Paginator::useBootstrapFive();

    // Compartir el tema a todas las vistas
    View::composer('*', function ($view) {
        $config = Configuracion::first();
        $tema = $config ? $config->tema : 'claro';
        $view->with('tema', $tema);
    });
}
}
