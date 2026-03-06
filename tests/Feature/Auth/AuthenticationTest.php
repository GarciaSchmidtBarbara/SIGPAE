<?php

namespace Tests\Feature\Auth;

use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_profesional_puede_autenticarse(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create(['activo' => true])->id_persona,
            'usuario'       => 'test.user',
            'contrasenia'   => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'usuario'    => 'test.user',
            'contrasenia' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('welcome');
    }

    public function test_profesional_no_puede_autenticarse_con_contrasenia_incorrecta(): void
    {
        Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
            'usuario'       => 'test.user',
            'contrasenia'   => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'usuario'    => 'test.user',
            'contrasenia' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_profesional_inactivo_no_puede_autenticarse(): void
    {
        $persona = Persona::factory()->create(['activo' => false]);
        Profesional::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'usuario'       => 'inactivo.user',
            'contrasenia'   => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'usuario'    => 'inactivo.user',
            'contrasenia' => 'password123',
        ]);

        $this->assertGuest();
    }

    public function test_profesional_puede_cerrar_sesion(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
        ]);

        $response = $this->actingAs($profesional)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
