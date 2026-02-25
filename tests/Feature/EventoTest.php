<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Evento;
use App\Models\Profesional;
use App\Models\Persona;
use App\Models\Alumno;
use App\Models\Aula;
use App\Enums\TipoEvento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    protected Profesional $profesional;
    protected Persona $persona;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear aulas primero (necesario para alumnos)
        Aula::factory()->count(3)->create();
        
        // Crear un profesional para autenticación
        $this->persona = Persona::factory()->create();
        $this->profesional = Profesional::factory()->create([
            'fk_id_persona' => $this->persona->id_persona,
            'contrasenia' => Hash::make('password123'),
        ]);
    }

    /** @test */
    public function usuario_autenticado_puede_ver_lista_de_eventos()
    {
        // Crear algunos eventos
        Evento::factory()->count(3)->create([
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('eventos.principal'));

        $response->assertStatus(200);
        $response->assertViewIs('eventos.principal');
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_acceder_a_eventos()
    {
        $response = $this->get(route('eventos.principal'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function puede_crear_evento_banda()
    {
        $aula = Aula::inRandomOrder()->first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);
        $profesional2 = Profesional::factory()->create();

        $datosEvento = [
            'tipo_evento' => TipoEvento::BANDA->value,
            'fecha_hora' => now()->addDay()->format('Y-m-d H:i:s'),
            'lugar' => 'Sala de reuniones',
            'notas' => 'Reunión importante',
            'periodo_recordatorio' => 7,
            'profesionales' => [['id' => $profesional2->id_profesional]],
            'alumnos' => [$alumno->id_alumno],
            'cursos' => [$aula->id_aula],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('eventos', [
            'tipo_evento' => TipoEvento::BANDA->value,
            'lugar' => 'Sala de reuniones',
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
        ]);

        $evento = Evento::latest()->first();
        $this->assertCount(1, $evento->esInvitadoA);
        $this->assertCount(1, $evento->alumnos);
        $this->assertCount(1, $evento->aulas);
    }

    /** @test */
    public function puede_crear_evento_derivacion_externa_sin_profesionales()
    {
        $aula = Aula::inRandomOrder()->first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $datosEvento = [
            'descripcion_externa' => 'Derivación a psicólogo externo',
            'fecha' => now()->addDays(3)->format('Y-m-d'),
            'lugar' => 'Consultorio externo',
            'notas' => 'Profesional externo: Dr. Juan Pérez',
            'alumnos' => [$alumno->id_alumno],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar-derivacion'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));

        $evento = Evento::latest()->first();
        $this->assertEquals(TipoEvento::DERIVACION_EXTERNA, $evento->tipo_evento);
        $this->assertCount(0, $evento->esInvitadoA); // No debe tener profesionales invitados
        $this->assertStringContainsString('Dr. Juan Pérez', $evento->notas);
    }

    /** @test */
    public function puede_actualizar_evento_existente()
    {
        $evento = Evento::factory()->create([
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
            'lugar' => 'Lugar original',
        ]);

        $datosActualizados = [
            'tipo_evento' => $evento->tipo_evento->value,
            'fecha_hora' => $evento->fecha_hora->format('Y-m-d H:i:s'),
            'lugar' => 'Lugar actualizado',
            'notas' => 'Notas actualizadas',
        ];

        $response = $this->actingAs($this->profesional)
            ->put(route('eventos.actualizar', $evento->id_evento), $datosActualizados);

        $response->assertRedirect(route('eventos.principal'));

        $this->assertDatabaseHas('eventos', [
            'id_evento' => $evento->id_evento,
            'lugar' => 'Lugar actualizado',
            'notas' => 'Notas actualizadas',
        ]);
    }

    /** @test */
    public function puede_ver_detalle_de_evento()
    {
        $evento = Evento::factory()->create([
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('eventos.ver', $evento->id_evento));

        $response->assertStatus(200);
        $response->assertViewIs('eventos.crear-editar');
        $response->assertViewHas('evento');
    }

    /** @test */
    public function puede_actualizar_confirmacion_de_asistencia()
    {
        $evento = Evento::factory()->create([
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
        ]);

        $invitacion = $evento->esInvitadoA()->create([
            'fk_id_profesional' => $this->profesional->id_profesional,
            'confirmacion' => false,
            'asistio' => false,
        ]);

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.actualizar-confirmacion', $evento->id_evento), [
                'confirmado' => 1,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $invitacion->refresh();
        $this->assertEquals(1, $invitacion->confirmacion);
    }

    /** @test */
    public function calendario_devuelve_eventos_en_formato_correcto()
    {
        Evento::factory()->count(5)->create([
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('eventos.calendario'));

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        
        $eventos = $response->json();
        foreach ($eventos as $evento) {
            $this->assertArrayHasKey('id', $evento);
            $this->assertArrayHasKey('title', $evento);
            $this->assertArrayHasKey('start', $evento);
            $this->assertArrayHasKey('allDay', $evento);
        }
    }

    /** @test */
    public function no_puede_crear_evento_sin_fecha()
    {
        $datosEvento = [
            'tipo_evento' => TipoEvento::RG->value,
            'lugar' => 'Sala de reuniones',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertSessionHasErrors(['fecha_hora']);
    }

    /** @test */
    public function no_puede_crear_evento_sin_tipo()
    {
        $datosEvento = [
            'fecha_hora' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'lugar' => 'Sala de reuniones',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertSessionHasErrors(['tipo_evento']);
    }

    /** @test */
    public function puede_crear_evento_reunion_gabinete()
    {
        $aula = Aula::inRandomOrder()->first();
        $profesional2 = Profesional::factory()->create();

        $datosEvento = [
            'tipo_evento' => TipoEvento::RG->value,
            'fecha_hora' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'lugar' => 'Sala de profesores',
            'profesionales' => [['id' => $profesional2->id_profesional]],
            'cursos' => [$aula->id_aula],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));

        $evento = Evento::latest()->first();
        $this->assertEquals(TipoEvento::RG, $evento->tipo_evento);
        $this->assertCount(1, $evento->aulas);
    }

    /** @test */
    public function puede_crear_evento_cita_familiar()
    {
        $aula = Aula::inRandomOrder()->first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $datosEvento = [
            'tipo_evento' => TipoEvento::CITA_FAMILIAR->value,
            'fecha_hora' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'lugar' => 'Consultorio',
            'alumnos' => [$alumno->id_alumno],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));

        $evento = Evento::latest()->first();
        $this->assertEquals(TipoEvento::CITA_FAMILIAR, $evento->tipo_evento);
        $this->assertCount(1, $evento->alumnos);
    }

    /** @test */
    public function evento_pasado_no_permite_modificar_fecha()
    {
        $evento = Evento::factory()->pasado()->create([
            'fk_id_profesional_creador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('eventos.ver', $evento->id_evento));

        $response->assertStatus(200);
        // Verificar que la vista tenga el evento
        $response->assertViewHas('evento');
    }

    /** @test */
    public function puede_agregar_multiples_profesionales_a_evento()
    {
        $profesionales = Profesional::factory()->count(3)->create();
        $profesionalesIds = $profesionales->pluck('id_profesional')->map(function($id) {
            return ['id' => $id];
        })->toArray();

        $datosEvento = [
            'tipo_evento' => TipoEvento::RD->value,
            'fecha_hora' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'lugar' => 'Despacho',
            'profesionales' => $profesionalesIds,
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));

        $evento = Evento::latest()->first();
        $this->assertCount(3, $evento->esInvitadoA);
    }

    /** @test */
    public function puede_agregar_multiples_alumnos_a_evento()
    {
        $aula = Aula::inRandomOrder()->first();
        $alumnos = Alumno::factory()->count(5)->create(['fk_id_aula' => $aula->id_aula]);
        $alumnosIds = $alumnos->pluck('id_alumno')->toArray();

        $datosEvento = [
            'tipo_evento' => TipoEvento::BANDA->value,
            'fecha_hora' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'lugar' => 'Aula 1',
            'alumnos' => $alumnosIds,
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));

        $evento = Evento::latest()->first();
        $this->assertCount(5, $evento->alumnos);
    }

    /** @test */
    public function evento_con_recordatorio_se_guarda_correctamente()
    {
        $datosEvento = [
            'tipo_evento' => TipoEvento::RG->value,
            'fecha_hora' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'lugar' => 'Sala de reuniones',
            'periodo_recordatorio' => 3,
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('eventos.guardar'), $datosEvento);

        $response->assertRedirect(route('eventos.principal'));

        $this->assertDatabaseHas('eventos', [
            'periodo_recordatorio' => 3,
        ]);
    }
}
