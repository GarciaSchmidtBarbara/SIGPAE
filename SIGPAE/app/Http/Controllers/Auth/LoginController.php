<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validar las credenciales
        $request->validate([
            'usuario' => 'required|string',
            'contrasenia' => 'required|string',
        ], [
            'usuario.required' => 'Por favor, ingresá tu usuario.',
            'contrasenia.required' => 'La contraseña es obligatoria.'
        ]);
        
        // El campo por defecto de Laravel es 'email', pero tu modelo usa 'usuario'
        $credentials = $request->only('usuario', 'contrasenia');

        // 2. Buscar al profesional por usuario
        $prof = \App\Models\Profesional::where('usuario', $request->usuario)->first();

        // 3. Verificar contraseña y loguear manualmente
        if ($prof && \Hash::check($request->contrasenia, $prof->contrasenia)) {
            Auth::guard('web')->login($prof);
            $request->session()->regenerate();
            return redirect()->intended('/welcome');
        }

        // 4. Autenticación fallida
        return back()->withErrors([
            'usuario' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('usuario'); // Mantener el nombre de usuario ingresado
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // Usar el guard explícito para mayor claridad
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}