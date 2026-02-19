<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Evento;
use App\Models\Profesional;
use App\Models\Alumno;
use App\Models\Aula;
use App\Enums\TipoEvento;
use App\Services\Implementations\EventoService;
use App\Repositories\Interfaces\EventoRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class EventoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventoService $eventoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventoService = app(EventoService::class);
    }

    /** @test */
    public function puede_obtener_eventos_para_calendario()
    {
        $profesional = Profesional::factory()->create();
        
        Evento::factory()->count(3)->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();

        $this->assertCount(3, $eventos);
        $this->assertArrayHasKey('id', $eventos[0]);
        $this->assertArrayHasKey('title', $eventos[0]);
        $this->assertArrayHasKey('start', $eventos[0]);
    }

    /** @test */
    public function formatea_titulo_evento_correctamente()
    {
        $profesional = Profesional::factory()->create();
        
        $eventoBanda = Evento::factory()->banda()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);
        
        $eventos = $this->eventoService->obtenerEventosParaCalendario();
        $evento = collect($eventos)->firstWhere('id', $eventoBanda->id_evento);

        $this->assertEquals('Banda', $evento['title']);
    }

    /** @test */
    public function eventos_incluyen_informacion_extendida()
    {
        $profesional = Profesional::factory()->create();
        
        Evento::factory()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
            'lugar' => 'Sala de reuniones',
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();

        $this->assertArrayHasKey('extendedProps', $eventos[0]);
        $this->assertArrayHasKey('lugar', $eventos[0]['extendedProps']);
        $this->assertEquals('Sala de reuniones', $eventos[0]['extendedProps']['lugar']);
    }

    /** @test */
    public function puede_crear_evento_con_datos_validos()
    {
        $profesional = Profesional::factory()->create();
        
        $datos = [
            'tipo_evento' => TipoEvento::BANDA->value,
            'fecha_hora' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'lugar' => 'Aula 1',
            'notas' => 'Reunión importante',
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ];

        $evento = $this->eventoService->crear($datos);

        $this->assertInstanceOf(Evento::class, $evento);
        $this->assertEquals(TipoEvento::BANDA, $evento->tipo_evento);
        $this->assertEquals('Aula 1', $evento->lugar);
    }

    /** @test */
    public function puede_actualizar_evento_existente()
    {
        $evento = Evento::factory()->create([
            'lugar' => 'Lugar original',
        ]);

        $datosActualizados = [
            'tipo_evento' => $evento->tipo_evento->value,
            'fecha_hora' => $evento->fecha_hora->format('Y-m-d H:i:s'),
            'lugar' => 'Lugar actualizado',
        ];

        $eventoActualizado = $this->eventoService->actualizar($evento->id_evento, $datosActualizados);

        $this->assertEquals('Lugar actualizado', $eventoActualizado->lugar);
    }

    /** @test */
    public function puede_obtener_evento_por_id()
    {
        $evento = Evento::factory()->create();

        $eventoObtenido = $this->eventoService->obtenerPorId($evento->id_evento);

        $this->assertInstanceOf(Evento::class, $eventoObtenido);
        $this->assertEquals($evento->id_evento, $eventoObtenido->id_evento);
    }

    /** @test */
    public function devuelve_null_cuando_evento_no_existe()
    {
        $eventoObtenido = $this->eventoService->obtenerPorId(99999);

        $this->assertNull($eventoObtenido);
    }

    /** @test */
    public function puede_listar_todos_los_eventos()
    {
        Evento::factory()->count(5)->create();

        $eventos = $this->eventoService->listar();

        $this->assertCount(5, $eventos);
    }

    /** @test */
    public function derivacion_externa_incluye_profesional_en_notas()
    {
        $profesional = Profesional::factory()->create();
        
        $datos = [
            'tipo_evento' => TipoEvento::DERIVACION_EXTERNA->value,
            'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'lugar' => 'Consultorio externo',
            'profesional_externo' => 'Dr. Carlos Martínez',
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ];

        $evento = $this->eventoService->crearDerivacionExterna($datos);

        $this->assertStringContainsString('Dr. Carlos Martínez', $evento->notas);
        $this->assertEquals(TipoEvento::DERIVACION_EXTERNA, $evento->tipo_evento);
    }

    /** @test */
    public function evento_banda_formatea_titulo_correctamente()
    {
        $profesional = Profesional::factory()->create();
        
        $evento = Evento::factory()->banda()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();
        $eventoCalendario = collect($eventos)->firstWhere('id', $evento->id_evento);

        $this->assertEquals('Banda', $eventoCalendario['title']);
    }

    /** @test */
    public function evento_rg_formatea_titulo_correctamente()
    {
        $profesional = Profesional::factory()->create();
        
        $evento = Evento::factory()->reunionGabinete()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();
        $eventoCalendario = collect($eventos)->firstWhere('id', $evento->id_evento);

        $this->assertEquals('Reunión Gabinete', $eventoCalendario['title']);
    }

    /** @test */
    public function evento_rd_formatea_titulo_correctamente()
    {
        $profesional = Profesional::factory()->create();
        
        $evento = Evento::factory()->reunionDerivacion()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();
        $eventoCalendario = collect($eventos)->firstWhere('id', $evento->id_evento);

        $this->assertEquals('Reunión Derivación', $eventoCalendario['title']);
    }

    /** @test */
    public function evento_cita_familiar_formatea_titulo_correctamente()
    {
        $profesional = Profesional::factory()->create();
        
        $evento = Evento::factory()->citaFamiliar()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();
        $eventoCalendario = collect($eventos)->firstWhere('id', $evento->id_evento);

        $this->assertEquals('Cita Familiar', $eventoCalendario['title']);
    }

    /** @test */
    public function evento_derivacion_externa_formatea_titulo_correctamente()
    {
        $profesional = Profesional::factory()->create();
        
        $evento = Evento::factory()->derivacionExterna()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();
        $eventoCalendario = collect($eventos)->firstWhere('id', $evento->id_evento);

        $this->assertEquals('Derivación Externa', $eventoCalendario['title']);
    }

    /** @test */
    public function eventos_tienen_formato_allday_true()
    {
        $profesional = Profesional::factory()->create();
        
        Evento::factory()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();

        $this->assertTrue($eventos[0]['allDay']);
    }

    /** @test */
    public function eventos_incluyen_hora_en_extended_props()
    {
        $profesional = Profesional::factory()->create();
        
        $fechaHora = now()->setTime(14, 30);
        Evento::factory()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
            'fecha_hora' => $fechaHora,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();

        $this->assertArrayHasKey('hora', $eventos[0]['extendedProps']);
        $this->assertEquals('14:30', $eventos[0]['extendedProps']['hora']);
    }

    /** @test */
    public function eventos_incluyen_nombre_creador_en_extended_props()
    {
        $profesional = Profesional::factory()->create();
        
        Evento::factory()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();

        $this->assertArrayHasKey('creador', $eventos[0]['extendedProps']);
        $this->assertNotEmpty($eventos[0]['extendedProps']['creador']);
    }

    /** @test */
    public function fecha_evento_usa_formato_correcto_para_calendario()
    {
        $profesional = Profesional::factory()->create();
        
        $fecha = now()->setDate(2025, 12, 15);
        Evento::factory()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
            'fecha_hora' => $fecha,
        ]);

        $eventos = $this->eventoService->obtenerEventosParaCalendario();

        $this->assertEquals('2025-12-15', $eventos[0]['start']);
    }
}
