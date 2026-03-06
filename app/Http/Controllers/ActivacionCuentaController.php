<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Interfaces\ProfesionalServiceInterface;

class ActivacionCuentaController extends Controller
{
    protected ProfesionalServiceInterface $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService)
    {
        $this->profesionalService = $profesionalService;
    }

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

        try {
            $prof = $this->profesionalService->activarCuenta(
                $request->email,
                $request->token,
                $request->only(['password', 'telefono', 'fecha_nacimiento', 'profesion', 'siglas'])
            );

            Auth::login($prof);

            return redirect()->route('welcome')
                ->with('status', 'Cuenta activada correctamente.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function desactivar()
    {
        $usuario = Auth::user();

        $this->profesionalService->desactivarCuenta($usuario->id_profesional);

        Auth::logout();

        return redirect()->route('login')
            ->with('success', 'Tu cuenta fue desactivada correctamente.');
    }
}
