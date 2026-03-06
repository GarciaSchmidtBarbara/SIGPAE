<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Interfaces\ProfesionalServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService) {
        $this->profesionalService = $profesionalService;
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'prof' => $request->user()->load('persona'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $profesional = $request->user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'profesion' => 'required|string|max:255',
            'usuario' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'siglas' => 'nullable',
            'telefono' => 'nullable|string|max:20',
            'hora_envio_resumen_diario' => 'nullable|date_format:H:i',
            'notification_anticipation_minutos' => 'nullable|integer|min:0',
        ]);

        try {
            $this->profesionalService->actualizarPerfil($profesional->id_profesional, $request->only([
                'nombre', 'apellido', 'profesion', 'usuario', 'email',
                'siglas', 'telefono', 'hora_envio_resumen_diario', 'notification_anticipation_minutos',
            ]));

            return back()->with('success', 'Perfil actualizado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el perfil.');
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->profesionalService->deleteProfesional($user->id_profesional);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function desactivar()
    {
        $usuario = Auth::user();

        $resultado = $this->profesionalService
            ->desactivarCuenta($usuario->id_profesional);

        if ($resultado) {
            Auth::logout();

            return redirect()->route('login')
                ->with('success', 'Tu cuenta fue desactivada correctamente.');
        }

        return back()->with('error', 'No pudo desactivarse la cuenta.');
    }
}
