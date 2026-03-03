<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordMail;
use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_puede_solicitarse(): void
    {
        Mail::fake();

        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
        ]);

        $this->post('/forgot-password', ['email' => $profesional->email]);

        Mail::assertSent(ResetPasswordMail::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
        ]);

        $token = Password::broker()->createToken($profesional);

        $response = $this->get('/reset-password/' . $token);

        $response->assertStatus(200);
    }

    public function test_contrasenia_puede_resetearse_con_token_valido(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
        ]);

        $token = Password::broker()->createToken($profesional);

        $response = $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $profesional->email,
            'password'              => 'nueva-contrasenia-123',
            'password_confirmation' => 'nueva-contrasenia-123',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }
}
