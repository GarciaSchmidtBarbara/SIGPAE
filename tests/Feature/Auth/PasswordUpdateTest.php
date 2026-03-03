<?php

namespace Tests\Feature\Auth;

use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_contrasenia_puede_actualizarse(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
            'contrasenia'   => Hash::make('contrasenia-actual'),
        ]);

        $response = $this
            ->actingAs($profesional)
            ->put('/password', [
                'current_password'      => 'contrasenia-actual',
                'password'              => 'nueva-contrasenia',
                'password_confirmation' => 'nueva-contrasenia',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('nueva-contrasenia', $profesional->refresh()->contrasenia));
    }

    public function test_contrasenia_actual_incorrecta_no_permite_actualizar(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
            'contrasenia'   => Hash::make('contrasenia-actual'),
        ]);

        $response = $this
            ->actingAs($profesional)
            ->put('/password', [
                'current_password'      => 'contrasenia-incorrecta',
                'password'              => 'nueva-contrasenia',
                'password_confirmation' => 'nueva-contrasenia',
            ]);

        $response->assertSessionHas('error');
        $this->assertTrue(Hash::check('contrasenia-actual', $profesional->refresh()->contrasenia));
    }

    public function test_contrasenia_nueva_requiere_confirmacion(): void
    {
        $profesional = Profesional::factory()->create([
            'fk_id_persona' => Persona::factory()->create()->id_persona,
            'contrasenia'   => Hash::make('contrasenia-actual'),
        ]);

        $response = $this
            ->actingAs($profesional)
            ->put('/password', [
                'current_password'      => 'contrasenia-actual',
                'password'              => 'nueva-contrasenia',
                'password_confirmation' => 'no-coincide',
            ]);

        $response->assertSessionHasErrors(['password']);
    }
}
