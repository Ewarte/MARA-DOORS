<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.auth.login');
    }
// comando para refrescar la base de datos php artisan migrate:refresh --seed
    public function login(LoginRequest $request)
    {
        // Validar credenciales
        if (!Auth::validate($request->only('email', 'password'))) {
            return redirect()->route('login')->withErrors('Credenciales incorrectas');
        }

        // Crear una sesion
        $user = Auth::getProvider()->retrieveByCredentials($request->only('email', 'password'));
        Auth::login($user);



        // Para otros usuarios, redirigir al panel normal
        return redirect()->route('panel')->with('success', 'Bienvenido ' . $user->name);
    }

    // Método adicional para logout (si es necesario)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Sesión cerrada correctamente');
    }
}



