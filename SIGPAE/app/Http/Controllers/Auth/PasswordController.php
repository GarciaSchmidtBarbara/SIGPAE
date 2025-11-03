<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller {
    public function showChangePasswordForm() {
    return view('auth.change-password');
}

public function changePassword(Request $request) {
    $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|confirmed|min:8',
    ],[
        'current_password' => 'Debes ingresar tu contraseña actual.',
        'new_password.required' => 'Debes ingresar la nueva contraseña.',
        'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
    ]);

    $user = Auth::user();

    if(!Hash::check($request->current_password, $user->contrasenia)) {
        return back()->withErrors(['current_password' => 'la contraseña actual es incorrecta.']);
    }

    $user->contrasenia = Hash::make($request->new_password);
    $user->save();

    return back()->with('status', 'Contraseña actualizada con exito.');
}
}