<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Evento;
use App\Models\Profesional;
use App\Models\Alumno;
use App\Models\Aula;
use App\Enums\TipoEvento;
use App\Repositories\Eloquent\EventoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected EventoRepository $eventoRepository;

    protected function setUp(): void
            
        // Crear aulas primero (necesario para alumnos)
        Aula::factory()->count(3)->create();
        
        $this->eventoRepository = app(EventoRepository::class);
    }

    /** @test */
    public function puede_crear_evento()
    {
        $profesional = Profesional::factory()->create();

        $datos = [
            'tipo_evento' => TipoEvento::BANDA,
            'fecha_hora' => now()->addDays(5),
            'lugar' => 'Sala de reuniones',
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ];

        $evento = $this->eventoRepository->create($datos);

        $this->assertInstanceOf(Evento::class, $evento);
        $this->assertEquals(TipoEvento::BANDA, $evento->tipo_evento);
        $this->assertDatabaseHas('eventos', [
            'id_evento' => $evento->id_evento,
            'lugar' => 'Sala de reuniones',
        ]);
    }

    /** @test */
    public function puede_obtener_evento_por_id()
    {
        $evento = Evento::factory()->create();

        $eventoObtenido = $this->eventoRepository->find($evento->id_evento);

        $this->assertNotNull($eventoObtenido);
        $this->assertEquals($evento->id_evento, $eventoObtenido->id_evento);
    }

    /** @test */
    public function puede_obtener_todos_los_eventos()
    {
        Evento::factory()->count(7)->create();

        $eventos = $this->eventoRepository->all();

        $this->assertCount(7, $eventos);
    }

    /** @test */
    public function puede_actualizar_evento()
    {
        $evento = Evento::factory()->create([
            'lugar' => 'Lugar original',
        ]);

        $datosActualizados = [
            'lugar' => 'Lugar actualizado',
        ];

        $eventoActualizado = $this->eventoRepository->update($evento->id_evento, $datosActualizados);

        $this->assertTrue($eventoActualizado);
        $this->assertDatabaseHas('eventos', [
            'id_evento' => $evento->id_evento,
            'lugar' => 'Lugar actualizado',
        ]);
    }

    /** @test */
    public function puede_eliminar_evento()
    {
        $evento = Evento::factory()->create();

        $resultado = $this->eventoRepository->delete($evento->id_evento);

        $this->assertTrue($resultado);
        $this->assertDatabaseMissing('eventos', [
            'id_evento' => $evento->id_evento,
        ]);
    }

    /** @test */
    public function puede_obtener_eventos_con_relaciones()
    {
        $profesional = Profesional::factory()->create();
        $evento = Evento::factory()->create([
            'fk_id_profesional_creador' => $profesional->id_profesional,
        ]);

        $aula = Aula::inRandomOrder()->first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);
        $evento->alumnos()->attach($alumno->id_alumno);

        $evento->aulas()->attach($aula->id_aula);

        $eventoConRelaciones = $this->eventoRepository->findWithRelations($evento->id_evento);

        $this->assertNotNull($eventoConRelaciones);
        $this->assertTrue($eventoConRelaciones->relationLoaded('alumnos'));
        $this->assertTrue($eventoConRelaciones->relationLoaded('aulas'));
        $this->assertTrue($eventoConRelaciones->relationLoaded('profesionalCreador'));
    }

    /** @test */
    public function puede_obtener_eventos_futuros()
    {
        // Crear eventos pasados
        Evento::factory()->count(3)->pasado()->create();
        
        // Crear eventos futuros
        Evento::factory()->count(5)->futuro()->create();

        $eventosFuturos = $this->eventoRepository->getFuturos();

        $this->assertCount(5, $eventosFuturos);
        foreach ($eventosFuturos as $evento) {
            $this->assertTrue($evento->fecha_hora->isFuture());
        }
    }

    /** @test */
    public function puede_obtener_eventos_pasados()
    {
        // Crear eventos futuros
        Evento::factory()->count(4)->futuro()->create();
        
        // Crear eventos pasados
        Evento::factory()->count(6)->pasado()->create();

        $eventosPasados = $this->eventoRepository->getPasados();

        $this->assertCount(6, $eventosPasados);
        foreach ($eventosPasados as $evento) {
            $this->assertTrue($evento->fecha_hora->isPast());
        }
    }

    /** @test */
    public function puede_obtener_eventos_por_tipo()
    {
        Evento::factory()->count(3)->banda()->create();
        Evento::factory()->count(2)->reunionGabinete()->create();
        Evento::factory()->count(4)->citaFamiliar()->create();

        $eventosBanda = $this->eventoRepository->getByTipo(TipoEvento::BANDA);
        $eventosRG = $this->eventoRepository->getByTipo(TipoEvento::RG);
        $eventosCitaFamiliar = $this->eventoRepository->getByTipo(TipoEvento::CITA_FAMILIAR);

        $this->assertCount(3, $eventosBanda);
        $this->assertCount(2, $eventosRG);
        $this->assertCount(4, $eventosCitaFamiliar);
    }

    /** @test */
    public function puede_obtener_eventos_por_profesional_creador()
    {
        $profesional1 = Profesional::factory()->create();
        $profesional2 = Profesional::factory()->create();

        Evento::factory()->count(5)->create([
            'fk_id_profesional_creador' => $profesional1->id_profesional,
        ]);

        Evento::factory()->count(3)->create([
            'fk_id_profesional_creador' => $profesional2->id_profesional,
        ]);

        $eventosProfesional1 = $this->eventoRepository->getByCreador($profesional1->id_profesional);
        $eventosProfesional2 = $this->eventoRepository->getByCreador($profesional2->id_profesional);

        $this->assertCount(5, $eventosProfesional1);
        $this->assertCount(3, $eventosProfesional2);
    }

    /** @test */
    public function puede_obtener_eventos_entre_fechas()
    {
        $fechaInicio = now()->addDays(5);
        $fechaFin = now()->addDays(15);

        // Eventos dentro del rango
        Evento::factory()->count(3)->create([
            'fecha_hora' => now()->addDays(10),
        ]);

        // Eventos fuera del rango
        Evento::factory()->count(2)->create([
            'fecha_hora' => now()->addDays(20),
        ]);

        $eventosEnRango = $this->eventoRepository->getBetweenDates($fechaInicio, $fechaFin);

        $this->assertCount(3, $eventosEnRango);
        foreach ($eventosEnRango as $evento) {
            $this->assertTrue($evento->fecha_hora->between($fechaInicio, $fechaFin));
        }
    }

    /** @test */
    public function puede_obtener_eventos_con_alumnos()
    {
        $aula = Aula::inRandomOrder()->first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);
        
        $evento1 = Evento::factory()->create();
        $evento1->alumnos()->attach($alumno->id_alumno);

        $evento2 = Evento::factory()->create();
        $evento2->alumnos()->attach($alumno->id_alumno);

        Evento::factory()->count(3)->create(); // Sin alumnos

        $eventosConAlumnos = $this->eventoRepository->getWithAlumnos();

        $this->assertCount(2, $eventosConAlumnos);
    }

    /** @test */
    public function puede_obtener_eventos_de_alumno_especifico()
    {
        $aula1 = Aula::inRandomOrder()->first();
        $aula2 = Aula::skip(1)->first();
        
        $alumno1 = Alumno::factory()->create(['fk_id_aula' => $aula1->id_aula]);
        $alumno2 = Alumno::factory()->create(['fk_id_aula' => $aula2->id_aula]);

        $evento1 = Evento::factory()->create();
        $evento1->alumnos()->attach($alumno1->id_alumno);

        $evento2 = Evento::factory()->create();
        $evento2->alumnos()->attach($alumno1->id_alumno);

        $evento3 = Evento::factory()->create();
        $evento3->alumnos()->attach($alumno2->id_alumno);

        $eventosAlumno1 = $this->eventoRepository->getByAlumno($alumno1->id_alumno);

        $this->assertCount(2, $eventosAlumno1);
    }

    /** @test */
    public function puede_obtener_eventos_con_recordatorio()
    {
        Evento::factory()->count(3)->create(['periodo_recordatorio' => null]);
        Evento::factory()->count(4)->create(['periodo_recordatorio' => 7]);

        $eventosConRecordatorio = $this->eventoRepository->getWithRecordatorio();

        $this->assertCount(4, $eventosConRecordatorio);
        foreach ($eventosConRecordatorio as $evento) {
            $this->assertNotNull($evento->periodo_recordatorio);
        }
    }

    /** @test */
    public function puede_contar_eventos_por_tipo()
    {
        Evento::factory()->count(5)->banda()->create();
        Evento::factory()->count(3)->reunionGabinete()->create();

        $contadorBanda = $this->eventoRepository->countByTipo(TipoEvento::BANDA);
        $contadorRG = $this->eventoRepository->countByTipo(TipoEvento::RG);

        $this->assertEquals(5, $contadorBanda);
        $this->assertEquals(3, $contadorRG);
    }

    /** @test */
    public function puede_verificar_si_evento_existe()
    {
        $evento = Evento::factory()->create();

        $existe = $this->eventoRepository->exists($evento->id_evento);
        $noExiste = $this->eventoRepository->exists(99999);

        $this->assertTrue($existe);
        $this->assertFalse($noExiste);
    }
}
