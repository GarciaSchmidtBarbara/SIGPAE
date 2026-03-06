<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ProfesionalServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    protected ProfesionalServiceInterface $profesionalService;

    public function __construct(ProfesionalServiceInterface $profesionalService)
    {
        $this->profesionalService = $profesionalService;
    }

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

        try {
            $this->profesionalService->resetContrasenia(
                $request->email,
                $request->token,
                $request->password
            );

            return redirect()->route('login')->with('status', 'Contraseña restablecida con éxito.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
    }
}
