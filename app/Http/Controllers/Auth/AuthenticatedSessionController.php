<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    //view
    public function create(): View
    {
        return view('auth.login');
    }

    //solicitud de verificación de login y redireccionamiento a la pagina de welcome
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); //valida las credenciales

        $request->session()->regenerate(); //regenera el ID de sesion

        return redirect()->intended('welcome'); //redirige al usuario
    }

    //cierra la sesion
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout(); //cierra la sesion

        $request->session()->invalidate(); //borra los datos de la sesion

        $request->session()->regenerateToken(); //genera un nuevo token

        return redirect('/'); //redirige al login
    }
}
