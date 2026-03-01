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
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'prof' => $request->user()->load('persona'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
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

        $profesional->persona->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
        ]);

        $profesional->persona->update([
            'profesion' => $request->profesion,
            'usuario' => $request->usuario,
            'email' => $request->email,
            'siglas' => $request->siglas,
            'telefono' => $request->telefono,
            'hora_envio_resumen_diario' => $request->hora_envio_resumen_diario,
            'notification_anticipation_minutos' => $request->notifiction_anticipation_minutos,
        ]);

        return back()->with('success', 'Perfil actualizado correctamente');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    protected $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService) {
        $this->profesionalService = $profesionalService;
    }

    public function desactivar()
    {
        $usuario = Auth::user();

        $resultado = $this->profesionalService
            ->cambiarActivo($usuario->id_profesional);

        if ($resultado) {
            Auth::logout();

            return redirect()->route('login')
                ->with('success', 'Tu cuenta fue desactivada correctamente.');
        }

        return back()->with('error', 'No pudo desactivarse la cuenta.');
    }
}
