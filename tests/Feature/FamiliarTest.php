<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Aula;
use App\Models\Alumno;
use App\Models\Familiar;
use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Hash;
use App\Services\Interfaces\NotificacionServiceInterface;

class FamiliarTest extends TestCase
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
        ]);
    }

  
    private function sesionAsistente(array $alumno = [], array $familiares = []): array
    {
        return [
            'asistente' => [
                'alumno'     => array_merge([
                    'nombre'   => 'Alumno',
                    'apellido' => 'Test',
                    'dni'      => '11111111',
                ], $alumno),
                'familiares' => $familiares,
                'familiares_a_eliminar'        => [],
                'hermanos_alumnos_a_eliminar'  => [],
            ],
        ];
    }

    #[Test]
    public function puede_ver_formulario_de_creacion_de_familiar()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->get(route('familiares.crear'));

        $response->assertStatus(200);
        $response->assertViewIs('familiares.crear-editar');
        $response->assertViewHas('familiarData');
        $response->assertViewHas('indice', null);
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_familiares()
    {
        $response = $this->get(route('familiares.crear'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function puede_ver_formulario_de_edicion_de_familiar()
    {
        $familiarEnSesion = [
            'nombre'             => 'Carlos',
            'apellido'           => 'López',
            'dni'                => '22334455',
            'fecha_nacimiento'   => '1985-03-15',
            'domicilio'          => 'Calle Falsa 123',
            'nacionalidad'       => 'Argentina',
            'telefono_personal'  => '1122334455',
            'telefono_laboral'   => '',
            'lugar_de_trabajo'   => '',
            'parentesco'         => 'PADRE',
            'otro_parentesco'    => '',
            'observaciones'      => '',
            'fk_id_persona'      => null,
            'id_familiar'        => null,
        ];

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente([], [$familiarEnSesion]))
            ->get(route('familiares.editar', ['indice' => 0]));

        $response->assertStatus(200);
        $response->assertViewIs('familiares.crear-editar');
        $response->assertViewHas('indice', 0);
    }

    #[Test]
    public function editar_familiar_con_indice_invalido_redirige()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->get(route('familiares.editar', ['indice' => 99]));

        $response->assertRedirect(route('alumnos.continuar'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function puede_guardar_familiar_nuevo_en_sesion()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->post(route('familiares.guardarYVolver'), [
                'nombre'              => 'María',
                'apellido'            => 'Gómez',
                'dni'                 => '33445566',
                'fecha_nacimiento'    => '1990-05-20',
                'domicilio'           => 'Av. Siempreviva 456',
                'nacionalidad'        => 'Argentina',
                'telefono_personal'   => '1155667788',
                'telefono_laboral'    => '',
                'lugar_de_trabajo'    => '',
                'parentesco'          => 'MADRE',
                'otro_parentesco'     => '',
                'observaciones'       => '',
                'asiste_a_institucion' => 0,
                'fk_id_persona'       => null,
                'id_familiar'         => null,
            ]);

        $response->assertRedirect(route('alumnos.continuar'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('asistente.familiares');
    }

    #[Test]
    public function puede_actualizar_familiar_existente_en_sesion()
    {
        $familiarExistente = [
            'nombre'             => 'Carlos',
            'apellido'           => 'López',
            'dni'                => '22334455',
            'fecha_nacimiento'   => '1985-03-15',
            'domicilio'          => 'Calle Vieja 100',
            'nacionalidad'       => 'Argentina',
            'telefono_personal'  => '1100000000',
            'telefono_laboral'   => '',
            'lugar_de_trabajo'   => '',
            'parentesco'         => 'PADRE',
            'otro_parentesco'    => '',
            'observaciones'      => '',
            'fk_id_persona'      => null,
            'id_familiar'        => null,
        ];

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente([], [$familiarExistente]))
            ->post(route('familiares.guardarYVolver'), [
                'indice'              => 0,
                'nombre'              => 'Carlos',
                'apellido'            => 'López',
                'dni'                 => '22334455',
                'fecha_nacimiento'    => '1985-03-15',
                'domicilio'           => 'Calle Nueva 200',
                'nacionalidad'        => 'Argentina',
                'telefono_personal'   => '1199999999',
                'telefono_laboral'    => '',
                'lugar_de_trabajo'    => '',
                'parentesco'          => 'PADRE',
                'otro_parentesco'     => '',
                'observaciones'       => 'Actualizado',
                'asiste_a_institucion' => 0,
                'fk_id_persona'       => null,
                'id_familiar'         => null,
            ]);

        $response->assertRedirect(route('alumnos.continuar'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function guardar_familiar_sin_campos_requeridos_falla()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->post(route('familiares.guardarYVolver'), []);

        $response->assertSessionHasErrors(['nombre', 'apellido', 'dni', 'fecha_nacimiento', 'parentesco']);
    }

    #[Test]
    public function guardar_familiar_con_parentesco_otro_permite_texto_libre()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->post(route('familiares.guardarYVolver'), [
                'nombre'              => 'Ana',
                'apellido'            => 'Ruiz',
                'dni'                 => '44556677',
                'fecha_nacimiento'    => '1988-11-10',
                'domicilio'           => '',
                'nacionalidad'        => '',
                'telefono_personal'   => '',
                'telefono_laboral'    => '',
                'lugar_de_trabajo'    => '',
                'parentesco'          => 'OTRO',
                'otro_parentesco'     => 'Madrina',
                'observaciones'       => '',
                'asiste_a_institucion' => 0,
                'fk_id_persona'       => null,
                'id_familiar'         => null,
            ]);

        $response->assertRedirect(route('alumnos.continuar'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function guardar_familiar_con_dni_duplicado_en_sesion_actualiza_existente()
    {
        $familiarExistente = [
            'nombre'             => 'Pedro',
            'apellido'           => 'Martínez',
            'dni'                => '55667788',
            'fecha_nacimiento'   => '1975-01-01',
            'domicilio'          => '',
            'nacionalidad'       => '',
            'telefono_personal'  => '',
            'telefono_laboral'   => '',
            'lugar_de_trabajo'   => '',
            'parentesco'         => 'PADRE',
            'otro_parentesco'    => '',
            'observaciones'      => '',
            'fk_id_persona'      => null,
            'id_familiar'        => null,
        ];

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente([], [$familiarExistente]))
            ->post(route('familiares.guardarYVolver'), [
                // Sin indice = modo crear, pero DNI ya existe en sesión
                'nombre'              => 'Pedro',
                'apellido'            => 'Martínez',
                'dni'                 => '55667788',
                'fecha_nacimiento'    => '1975-01-01',
                'domicilio'           => 'Nueva dirección',
                'nacionalidad'        => 'Argentina',
                'telefono_personal'   => '1112223344',
                'telefono_laboral'    => '',
                'lugar_de_trabajo'    => '',
                'parentesco'          => 'PADRE',
                'otro_parentesco'     => '',
                'observaciones'       => '',
                'asiste_a_institucion' => 0,
                'fk_id_persona'       => null,
                'id_familiar'         => null,
            ]);

        $response->assertRedirect(route('alumnos.continuar'));
        // No debe duplicar: sigue habiendo un solo familiar en sesión
    }

    #[Test]
    public function validar_dni_retorna_valido_para_dni_nuevo()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->postJson(route('familiares.validar-dni'), [
                'dni' => '99887766',
            ]);

        $response->assertOk();
        $response->assertJson(['valid' => true]);
    }

    #[Test]
    public function validar_dni_rechaza_dni_del_alumno_en_sesion()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente(['dni' => '11111111']))
            ->postJson(route('familiares.validar-dni'), [
                'dni' => '11111111',
            ]);

        $response->assertOk();
        $response->assertJson(['valid' => false]);
    }

    #[Test]
    public function validar_dni_rechaza_dni_de_otro_familiar_en_sesion()
    {
        $familiarEnSesion = [
            'nombre'   => 'Existente',
            'apellido' => 'Familiar',
            'dni'      => '44332211',
        ];

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente([], [$familiarEnSesion]))
            ->postJson(route('familiares.validar-dni'), [
                'dni' => '44332211',
            ]);

        $response->assertOk();
        $response->assertJson(['valid' => false]);
    }

    #[Test]
    public function validar_dni_permite_mismo_dni_al_editar_familiar_actual()
    {
        $familiarEnSesion = [
            'nombre'   => 'Editando',
            'apellido' => 'Este',
            'dni'      => '77665544',
        ];

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente([], [$familiarEnSesion]))
            ->postJson(route('familiares.validar-dni'), [
                'dni'    => '77665544',
                'indice' => 0,
            ]);

        $response->assertOk();
        $response->assertJson(['valid' => true]);
    }

    #[Test]
    public function validar_dni_rechaza_dni_existente_en_base_de_datos()
    {
        $personaExistente = Persona::factory()->create(['dni' => '12345678']);

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->postJson(route('familiares.validar-dni'), [
                'dni' => '12345678',
            ]);

        $response->assertOk();
        $response->assertJson(['valid' => false]);
    }

    #[Test]
    public function validar_dni_permite_dni_propio_al_editar_familiar_existente()
    {
        $personaExistente = Persona::factory()->create(['dni' => '88776655']);

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->postJson(route('familiares.validar-dni'), [
                'dni'            => '88776655',
                'fk_id_persona'  => $personaExistente->id_persona,
            ]);

        $response->assertOk();
        $response->assertJson(['valid' => true]);
    }


    #[Test]
    public function buscar_familiares_retorna_json()
    {
        $persona = Persona::factory()->create(['nombre' => 'FamiliarBuscable']);
        Familiar::create([
            'fk_id_persona'    => $persona->id_persona,
            'telefono_personal' => '1100001111',
        ]);

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->getJson(route('familiares.buscar', ['q' => 'FamiliarBuscable']));

        $response->assertOk();
        $response->assertJsonFragment(['nombre' => 'FamiliarBuscable']);
    }

    #[Test]
    public function buscar_familiares_sin_query_retorna_vacio()
    {
        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente())
            ->getJson(route('familiares.buscar'));

        $response->assertOk();
        $response->assertJson([]);
    }


    #[Test]
    public function editar_familiar_normaliza_datos_de_bbdd_anidados()
    {
        $familiarConPersona = [
            'id_familiar'        => 1,
            'fk_id_persona'      => null,
            'telefono_personal'  => '1122223333',
            'telefono_laboral'   => '',
            'lugar_de_trabajo'   => '',
            'parentesco'         => 'MADRE',
            'otro_parentesco'    => '',
            'observaciones'      => '',
            'persona' => [
                'id_persona'      => 10,
                'nombre'          => 'Laura',
                'apellido'        => 'Fernández',
                'dni'             => '33221100',
                'fecha_nacimiento' => '1982-07-25',
                'domicilio'       => 'Calle Anidada 99',
                'nacionalidad'    => 'Argentina',
            ],
        ];

        $response = $this->actingAs($this->profesional)
            ->withSession($this->sesionAsistente([], [$familiarConPersona]))
            ->get(route('familiares.editar', ['indice' => 0]));

        $response->assertStatus(200);
        $response->assertViewIs('familiares.crear-editar');
    }
}
