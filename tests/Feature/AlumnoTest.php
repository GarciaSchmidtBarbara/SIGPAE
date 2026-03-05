<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Hash;
use App\Services\Interfaces\NotificacionServiceInterface;

class AlumnoTest extends TestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(NotificacionServiceInterface::class);

        Aula::factory()->create();
        Aula::factory()->create();
        Aula::factory()->create();

        $persona = Persona::factory()->create();
        $this->profesional = Profesional::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'contrasenia'   => Hash::make('password123'),
        ]);
    }

    #[Test]
    public function usuario_autenticado_puede_ver_lista_de_alumnos()
    {
        $aula = Aula::first();
        Alumno::factory()->count(3)->create(['fk_id_aula' => $aula->id_aula]);

        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.principal'));

        $response->assertStatus(200);
        $response->assertViewIs('alumnos.principal');
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_crear_alumnos()
    {
        // La ruta de listado no tiene middleware auth,
        // pero la creación sí lo requiere
        $response = $this->get(route('alumnos.crear'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.crear'));

        $response->assertStatus(200);
        $response->assertViewIs('alumnos.crear-editar');
    }

    #[Test]
    public function puede_crear_alumno_con_datos_validos()
    {
        $aula = Aula::first();

        // Simular la sesión del asistente con familiares vacíos
        $this->withSession([
            'asistente' => [
                'alumno' => [],
                'familiares' => [],
                'familiares_a_eliminar' => [],
                'hermanos_alumnos_a_eliminar' => [],
            ],
        ]);

        $datos = [
            'dni'                => '99999001',
            'nombre'             => 'Juan',
            'apellido'           => 'Pérez',
            'fecha_nacimiento'   => '2018-05-15',
            'nacionalidad'       => 'Argentina',
            'aula'               => $aula->descripcion, // formato "1°A"
            'inasistencias'      => 5,
            'cud'                => 'No',
            'situacion_escolar'  => 'Sin observaciones',
            'observaciones'      => 'Alumno de prueba',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('alumnos.guardar'), $datos);

        $response->assertRedirect(route('alumnos.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('personas', [
            'dni'      => '99999001',
            'nombre'   => 'Juan',
            'apellido' => 'Pérez',
        ]);
    }

    #[Test]
    public function no_puede_crear_alumno_sin_dni()
    {
        $aula = Aula::first();

        $this->withSession([
            'asistente' => [
                'alumno' => [],
                'familiares' => [],
                'familiares_a_eliminar' => [],
                'hermanos_alumnos_a_eliminar' => [],
            ],
        ]);

        $datos = [
            'nombre'           => 'Juan',
            'apellido'         => 'Pérez',
            'fecha_nacimiento' => '2018-05-15',
            'aula'             => $aula->id_aula,
            'cud'              => 'No',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('alumnos.guardar'), $datos);

        $response->assertSessionHasErrors(['dni']);
    }

    #[Test]
    public function no_puede_crear_alumno_con_dni_duplicado()
    {
        $aula = Aula::first();
        $personaExistente = Persona::factory()->create(['dni' => '11111111']);

        $this->withSession([
            'asistente' => [
                'alumno' => [],
                'familiares' => [],
                'familiares_a_eliminar' => [],
                'hermanos_alumnos_a_eliminar' => [],
            ],
        ]);

        $datos = [
            'dni'              => '11111111',
            'nombre'           => 'Otro',
            'apellido'         => 'Alumno',
            'fecha_nacimiento' => '2018-01-01',
            'aula'             => $aula->id_aula,
            'cud'              => 'No',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('alumnos.guardar'), $datos);

        $response->assertSessionHasErrors(['dni']);
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $aula = Aula::first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.editar', $alumno->id_alumno));

        $response->assertStatus(200);
        $response->assertViewIs('alumnos.crear-editar');
    }

    #[Test]
    public function puede_cambiar_estado_activo_de_alumno()
    {
        $aula = Aula::first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);
        $estadoOriginal = $alumno->persona->activo;

        $response = $this->actingAs($this->profesional)
            ->patch(route('alumnos.cambiarActivo', $alumno->id_alumno));

        $response->assertRedirect(route('alumnos.principal'));
        $response->assertSessionHas('success');

        $alumno->persona->refresh();
        $this->assertNotEquals($estadoOriginal, $alumno->persona->activo);
    }

    #[Test]
    public function busqueda_de_alumnos_devuelve_json()
    {
        $aula = Aula::first();
        $persona = Persona::factory()->create(['nombre' => 'Facundo', 'apellido' => 'García']);
        Alumno::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'fk_id_aula'    => $aula->id_aula,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.buscar', ['q' => 'Facundo']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'Facundo']);
    }

    #[Test]
    public function busqueda_para_plan_devuelve_formato_correcto()
    {
        $aula = Aula::first();
        $persona = Persona::factory()->create(['nombre' => 'María', 'apellido' => 'López']);
        Alumno::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'fk_id_aula'    => $aula->id_aula,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.buscar-para-plan', ['q' => 'María']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'María']);

        $alumnos = $response->json();
        $this->assertArrayHasKey('id', $alumnos[0]);
        $this->assertArrayHasKey('nombre', $alumnos[0]);
        $this->assertArrayHasKey('apellido', $alumnos[0]);
    }

    #[Test]
    public function busqueda_para_plan_por_aula_devuelve_alumnos()
    {
        $aula = Aula::first();
        Alumno::factory()->count(3)->create([
            'fk_id_aula' => $aula->id_aula,
            'fk_id_persona' => Persona::factory()->state(['activo' => true]),
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.buscar-para-plan', ['aula_id' => $aula->id_aula]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, count($response->json()));
    }

    #[Test]
    public function validar_dni_ajax_devuelve_valido_para_dni_nuevo()
    {
        $this->withSession([
            'asistente' => [
                'alumno' => [],
                'familiares' => [],
            ],
        ]);

        $response = $this->actingAs($this->profesional)
            ->post(route('alumnos.validar-dni'), ['dni' => '99998888']);

        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }

    #[Test]
    public function validar_dni_ajax_devuelve_invalido_para_dni_existente()
    {
        Persona::factory()->create(['dni' => '12345678']);

        $this->withSession([
            'asistente' => [
                'alumno' => [],
                'familiares' => [],
            ],
        ]);

        $response = $this->actingAs($this->profesional)
            ->post(route('alumnos.validar-dni'), ['dni' => '12345678']);

        $response->assertStatus(200);
        $response->assertJson(['valid' => false]);
    }

    #[Test]
    public function sincronizar_estado_del_asistente()
    {
        $this->withSession([
            'asistente' => [
                'alumno' => [],
                'familiares' => [],
                'familiares_a_eliminar' => [],
                'hermanos_alumnos_a_eliminar' => [],
            ],
        ]);

        $datos = [
            'alumno' => [
                'nombre' => 'Test',
                'apellido' => 'Sync',
                'dni' => '55555555',
            ],
            'familiares' => [],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('asistente.sincronizar'), $datos);

        $response->assertStatus(204);
    }

    #[Test]
    public function puede_filtrar_alumnos_por_curso()
    {
        $aula = Aula::first();
        Alumno::factory()->count(2)->create(['fk_id_aula' => $aula->id_aula]);

        $response = $this->actingAs($this->profesional)
            ->get(route('alumnos.principal', ['curso' => $aula->curso]));

        $response->assertStatus(200);
        $response->assertViewIs('alumnos.principal');
    }
}
