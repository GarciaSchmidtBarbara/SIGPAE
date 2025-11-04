<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Profesional;

class ForgotPasswordController extends Controller
{
    private const TOKEN_EXPIRATION_MINUTES = 60;
    //view de formulario para pedir mail
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    //generación de token y simulación de envio
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $prof = profesional::where('email', $request->email)->first();

        if(!$prof) {
            return back() -> withErrors(['email' => 'No se encontro el correo.']);
        }

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $prof->email],
            ['token' => Hash::make($token),
             'created_at' => Carbon::now(),
            ]
        );

        Log::info("Token de recuperación para {$prof->email}: {$token}");
        return back()->with('status', 'Token negerado (Revisar log para verlo).');
    }
    //view para formulario para cambiar la contraseña
    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }
    //cambio de contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Token inválido o expirado.']);
        }

        //verificar expiración
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > self::TOKEN_EXPIRATION_MINUTES) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'El token ha expirado. Generá uno nuevo.']);
        }
        // Buscar profesional
        $prof = Profesional::where('email', $request->email)->first();
        if (!$prof) {
            return back()->withErrors(['email' => 'Usuario no encontrado.']);
        }

         // Actualizar la contraseña
        $prof->contrasenia = Hash::make($request->password);
        $prof->save();

        // Borrar el token usado
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login.form')->with('status', 'Contraseña restablecida con éxito.');
    }

    public function showEnterTokenForm()
    {
        return view('auth.enter-token');
    }
    public function verifyToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $record = DB::table('password_resets')->get()->first(function ($r) use ($request) {
            return Hash::check($request->token, $r->token);
        });
        if (!$record) {
            return back()->withErrors(['token' => 'Token inválido.']);
        }
        
        return redirect()->route('password.reset.form', ['token' => $request->token]);
    }
}
