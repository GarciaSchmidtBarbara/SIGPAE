<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Aula;
use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Services\Interfaces\NotificacionServiceInterface;

class ProfesionalTest extends TestCase
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
    public function usuario_autenticado_puede_ver_lista_de_usuarios()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('usuarios.principal'));

        $response->assertStatus(200);
        $response->assertViewIs('usuarios.principal');
    }

    #[Test]
    public function lista_de_usuarios_muestra_profesionales_existentes()
    {
        Profesional::factory()->create();

        $response = $this->actingAs($this->profesional)
            ->get(route('usuarios.principal'));

        $response->assertStatus(200);
        $response->assertViewHas('usuarios');
    }

    #[Test]
    public function puede_filtrar_usuarios_por_busqueda()
    {
        $persona = Persona::factory()->create(['nombre' => 'Buscable']);
        Profesional::factory()->create(['fk_id_persona' => $persona->id_persona]);

        $response = $this->actingAs($this->profesional)
            ->get(route('usuarios.principal', ['buscar' => 'Buscable']));

        $response->assertStatus(200);
        $response->assertViewHas('usuarios');
    }


    #[Test]
    public function puede_ver_formulario_de_creacion_de_usuario()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('usuarios.crear-editar'));

        $response->assertStatus(200);
        $response->assertViewIs('usuarios.crear-editar');
    }

    #[Test]
    public function puede_crear_usuario_con_datos_validos()
    {
        Password::shouldReceive('broker')
            ->with('profesionales')
            ->andReturnSelf();
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->actingAs($this->profesional)
            ->post(route('usuarios.store'), [
                'nombre'   => 'María',
                'apellido' => 'González',
                'dni'      => '33445566',
                'email'    => 'maria.gonzalez@test.gob.ar',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('personas', [
            'nombre'   => 'María',
            'apellido' => 'González',
            'dni'      => '33445566',
        ]);

        $this->assertDatabaseHas('profesionales', [
            'email' => 'maria.gonzalez@test.gob.ar',
        ]);
    }

    #[Test]
    public function no_puede_crear_usuario_con_dni_duplicado()
    {
        $dniExistente = $this->profesional->persona->dni;

        $response = $this->actingAs($this->profesional)
            ->post(route('usuarios.store'), [
                'nombre'   => 'Juan',
                'apellido' => 'Pérez',
                'dni'      => $dniExistente,
                'email'    => 'juan.perez@test.gob.ar',
            ]);

        $response->assertSessionHasErrors('dni');
    }

    #[Test]
    public function no_puede_crear_usuario_con_email_duplicado()
    {
        $emailExistente = $this->profesional->email;

        $response = $this->actingAs($this->profesional)
            ->post(route('usuarios.store'), [
                'nombre'   => 'Juan',
                'apellido' => 'Pérez',
                'dni'      => '99887766',
                'email'    => $emailExistente,
            ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function no_puede_crear_usuario_sin_campos_requeridos()
    {
        $response = $this->actingAs($this->profesional)
            ->post(route('usuarios.store'), []);

        $response->assertSessionHasErrors(['nombre', 'apellido', 'dni', 'email']);
    }


    #[Test]
    public function puede_actualizar_usuario()
    {
        $otro = Profesional::factory()->create();

        $response = $this->actingAs($this->profesional)
            ->put(route('usuarios.update', $otro->id_profesional), [
                'nombre'    => 'NombreActualizado',
                'apellido'  => 'ApellidoActualizado',
                'profesion' => 'Psicólogo',
                'siglas'    => 'PS',
            ]);

        $response->assertRedirect(route('usuarios.principal'));
    }


    #[Test]
    public function puede_cambiar_estado_activo_de_usuario()
    {
        $otro = Profesional::factory()->create(['activo' => true]);

        $response = $this->actingAs($this->profesional)
            ->patch(route('usuarios.cambiarActivo', $otro->id_profesional));

        $response->assertRedirect(route('usuarios.principal'));
        $response->assertSessionHas('success');
    }


    #[Test]
    public function usuario_autenticado_puede_ver_su_perfil()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('perfil.principal'));

        $response->assertStatus(200);
        $response->assertViewIs('perfil.principal');
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_ver_perfil()
    {
        $response = $this->get(route('perfil.principal'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function puede_actualizar_perfil_con_datos_validos()
    {
        $response = $this->actingAs($this->profesional)
            ->post(route('perfil.actualizar'), [
                'nombre'    => 'NuevoNombre',
                'apellido'  => 'NuevoApellido',
                'profesion' => 'Psicopedagogo',
                'siglas'    => 'PG',
                'usuario'   => 'nuevo.usuario',
                'email'     => 'nuevo@test.gob.ar',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->profesional->refresh();
        $this->assertEquals('Psicopedagogo', $this->profesional->profesion);
        $this->assertEquals('nuevo@test.gob.ar', $this->profesional->email);

        $this->profesional->persona->refresh();
        $this->assertEquals('NuevoNombre', $this->profesional->persona->nombre);
    }

    #[Test]
    public function no_puede_actualizar_perfil_con_email_de_otro_usuario()
    {
        $otro = Profesional::factory()->create();

        $response = $this->actingAs($this->profesional)
            ->post(route('perfil.actualizar'), [
                'nombre'    => 'Test',
                'apellido'  => 'Test',
                'profesion' => 'Psicólogo',
                'siglas'    => 'PS',
                'usuario'   => 'test.user',
                'email'     => $otro->email,
            ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function no_puede_actualizar_perfil_sin_campos_requeridos()
    {
        $response = $this->actingAs($this->profesional)
            ->post(route('perfil.actualizar'), []);

        $response->assertSessionHasErrors(['nombre', 'apellido', 'profesion', 'siglas', 'usuario', 'email']);
    }

    #[Test]
    public function puede_actualizar_perfil_manteniendo_su_propio_email()
    {
        $response = $this->actingAs($this->profesional)
            ->post(route('perfil.actualizar'), [
                'nombre'    => 'MismoEmail',
                'apellido'  => 'Test',
                'profesion' => 'Psicólogo',
                'siglas'    => 'PS',
                'usuario'   => $this->profesional->usuario,
                'email'     => $this->profesional->email,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
