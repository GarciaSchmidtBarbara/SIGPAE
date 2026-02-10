<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Debe ingresar su contraseña actual.',
            'password.required' => 'Debe ingresar una nueva contraseña.',
            'password.confirmed' => 'La confirmación no coincide con la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $prof = Auth::user();

        if (!Hash::check($request->current_password, $prof->contrasenia)) {
            return back()->with('error', 'La contraseña actual no es correcta.')->withInput();
        }

        $prof->contrasenia = Hash::make($request->password);
        $prof->save();

        return back()->with('success', 'Tu contraseña fue cambiada correctamente.');
    }
}