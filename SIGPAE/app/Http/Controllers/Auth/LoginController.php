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
            'password' => 'required|string',
        ]);
        
        // El campo por defecto de Laravel es 'email', pero tu modelo usa 'usuario'
        $credentials = $request->only('usuario', 'password');

        // 2. Intentar autenticar usando el guard 'web' (que ahora apunta a 'profesionales' en config/auth.php)
        // Forzamos el uso de Auth::guard('web')->attempt() para asegurarnos de que usa la configuración correcta.
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            
            // 3. La autenticación fue exitosa.
            // Para resolver el error SQLSTATE[42703] que persiste, la solución es la limpieza total,
            // pero el código de Laravel en sí es correcto.
            
            $request->session()->regenerate();
            
            // Forzar una redirección limpia
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

        return redirect('/login');
    }
}