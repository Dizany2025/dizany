<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:usuarios,email'],
        ]);

        // 1. Generar token
        $token = Str::random(64);

        // 2. Eliminar tokens anteriores
        DB::table('password_resets')->where('email', $request->email)->delete();

        // 3. Insertar nuevo token
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        // 4. Crear URL con firma segura (opcional pero recomendado)
        $url = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            ['token' => $token, 'email' => $request->email]
        );

        // 5. Enviar correo (personaliza aquí si quieres)
        Mail::send('auth.passwords.email-reset-link', ['url' => $url], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Restablecer contraseña - Dizany');
        });

        return back()->with('success', 'Te hemos enviado un enlace para restablecer tu contraseña.');
    }
}
