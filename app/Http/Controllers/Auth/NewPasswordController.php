<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $prof = Profesional::where('email', $request->email)->first();

        if (!$prof) {
            return back()->withErrors(['email' => 'No se encontró el correo.'])->withInput();
        }

        // Verificar token
        $record = \DB::table('password_resets')->where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Token inválido o expirado.'])->withInput();
        }

        // Actualizar contraseña
        $prof->contrasenia = Hash::make($request->password);
        $prof->save();

        // Borrar token
        \DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Contraseña restablecida con éxito.');
    }
}
