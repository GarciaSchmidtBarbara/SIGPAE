<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Profesional;

class ActivacionCuentaController extends Controller
{
    public function create(Request $request, $token){
        return view('auth.activar-cuenta', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'telefono' => ['required'],
            'fecha_nacimiento' => ['required', 'date'],
            'profesion' => ['required'],
            'siglas' => ['required'],
        ]);

        $prof = Profesional::where('email', $request->email)->first();

        if (!$prof) {
            return back()->withErrors(['email' => 'No se encontró el correo.']);
        }

        $record = \DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Token inválido o expirado.']);
        }

        // Guardar datos
        $prof->persona->fecha_nacimiento = $request->fecha_nacimiento;

        $prof->persona->save();
        

        $prof->contrasenia = Hash::make($request->password);
        $prof->telefono = $request->telefono;
        $prof->profesion = $request->profesion;
        $prof->siglas = $request->siglas;

        $prof->save();

        \DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        Auth::login($prof);

        return redirect()->route('welcome')
            ->with('status', 'Cuenta activada correctamente.');
    }

    public function desactivar()
    {
        $usuario = Auth::user();

        // Si el activo está en persona
        $usuario->persona->activo = false;
        $usuario->persona->save();

        // Cerrar sesión
        Auth::logout();

        return redirect()->route('login')
            ->with('success', 'Tu cuenta fue desactivada correctamente.');
    }
}
