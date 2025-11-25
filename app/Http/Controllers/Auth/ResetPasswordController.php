<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token)
    {
        // Verificar que el token exista
        $reset = DB::table('password_resets')
            ->where('token', $token)
            ->where('email', $request->email)
            ->first();

        if (!$reset) {
            return redirect()->route('login')->with('error', 'El token no es válido o ha expirado.');
        }

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:usuarios,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => 'Token inválido o expirado.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No se encontró ningún usuario con este correo.']);
        }

        $user->clave = Hash::make($request->password); // ⚠️ usamos 'clave' porque así se llama en tu DB
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Tu contraseña ha sido restablecida con éxito.');
    }
}
