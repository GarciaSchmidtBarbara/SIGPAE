<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Aula;
use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Hash;
use App\Services\Interfaces\NotificacionServiceInterface;

class ProfileTest extends TestCase
{
    use DatabaseTruncation;

    protected Profesional $profesional;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(NotificacionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('crear')->andReturn();
        });

        Aula::factory()->create();
        Aula::factory()->create();
        Aula::factory()->create();

        $persona = Persona::factory()->create();
        $this->profesional = Profesional::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'contrasenia'   => Hash::make('password123'),
            'activo'        => true,
        ]);
    }


    #[Test]
    public function usuario_puede_desactivar_su_propia_cuenta()
    {
        $response = $this->actingAs($this->profesional)
            ->patch(route('perfil.desactivar'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->profesional->persona->refresh();
        $this->assertFalse((bool) $this->profesional->persona->activo);
    }

    #[Test]
    public function usuario_queda_deslogueado_al_desactivar_cuenta()
    {
        $this->actingAs($this->profesional)
            ->patch(route('perfil.desactivar'));

        $this->assertGuest();
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_desactivar_cuenta()
    {
        $response = $this->patch(route('perfil.desactivar'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function usuario_desactivado_no_puede_iniciar_sesion()
    {
        // Primero desactivamos
        $this->actingAs($this->profesional)
            ->patch(route('perfil.desactivar'));

        // Intentamos login
        $response = $this->post(route('login'), [
            'usuario' => $this->profesional->usuario,
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }
}
