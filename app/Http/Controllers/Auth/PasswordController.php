<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ProfesionalServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    protected ProfesionalServiceInterface $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService)
    {
        $this->profesionalService = $profesionalService;
    }

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

        $this->profesionalService->actualizarContrasenia($prof->id_profesional, $request->password);

        return back()->with('success', 'Tu contraseña fue cambiada correctamente.');
    }
}