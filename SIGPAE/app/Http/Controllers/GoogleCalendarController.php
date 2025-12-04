<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Profesional;

class GoogleCalendarController extends Controller
{
    //redireccionamiento a la pagina de autentificacion de google
    public function redirectToGoogle(){
        //scopes necesarios para calendar y gmail
        $scopes = [
            \Google\Service\Calendar::CALENDAR,
            \Google\Service\Gmail::GMAIL_SEND, //solo envia correos
            'profile', //en caso de que quieran sacar algun dato
        ];

        return Socialite::driver('google')
            ->scopes($scopes)
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    //manejo de callback
    public function handleGoogleCallback(){
        try{
            $googleUser = Socialite::driver('google')->user();

            $profesional=Profesional::where('email', $googleUser->getEmail())->first();

            if($profesional) {
                $this->saveGoogleTokens($profesional, $googleUser);
                return redirect('/welcome') ->with('success', 'Calendario de google sincronizado correctamente.');
            } else {
                return redirect('/welcome') ->with('error', 'El email de google no coincide con el registrado.');
            }
        } catch(\Exception $e) {
            dd($e->getMessage());
        }
    }

    protected function saveGoogleTokens(Profesional $profesional, $googleUser)
    {
        $profesional->google_access_token = $googleUser->token;

        if($googleUser->refreshToken){
            $profesional->google_refresh_token = $googleUser->refreshToken;
        }

        $profesional->google_token_expires_at = now()->addSeconds($googleUser->expiresIn);

        $profesional->save();
    }
}
