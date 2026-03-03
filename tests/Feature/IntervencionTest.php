<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Intervencion;
use App\Models\Persona;
use App\Models\PlanDeAccion;
use App\Models\Profesional;
use App\Enums\Modalidad;
use App\Enums\TipoIntervencion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class IntervencionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        Aula::factory()->count(3)->create();

        $persona = Persona::factory()->create();
        $this->profesional = Profesional::factory()->create([
            'fk_id_persona' => $persona->id_persona,
            'contrasenia'   => Hash::make('password123'),
        ]);
    }

    #[Test]
    public function usuario_autenticado_puede_ver_lista_de_intervenciones()
    {
        Intervencion::factory()->count(3)->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('intervenciones.principal'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('intervenciones.crear'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_crear_intervencion_con_datos_validos()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);
        $aula = Aula::inRandomOrder()->first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $datos = [
            'fecha_hora_intervencion' => now()->subDay()->format('Y-m-d'),
            'hora_intervencion'       => now()->subDay()->format('H:i:s'),
            'lugar'                   => 'Aula 3',
            'modalidad'               => Modalidad::PRESENCIAL->value,
            'temas_tratados'          => 'Dificultades de atención',
            'compromisos'             => 'Ninguno',
            'tipo_intervencion'       => TipoIntervencion::PROGRAMADA->value,
            'plan_de_accion'          => $plan->id_plan_de_accion,
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'alumnos'                 => [$alumno->id_alumno],
            'profesionales'           => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('intervenciones.guardar'), $datos);

        $response->assertRedirect();
        $this->assertDatabaseHas('intervenciones', [
            'lugar'    => 'Aula 3',
            'modalidad' => Modalidad::PRESENCIAL->value,
        ]);
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $intervencion = Intervencion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('intervenciones.editar', $intervencion->id_intervencion));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_actualizar_una_intervencion()
    {
        $intervencion = Intervencion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'lugar' => 'Lugar original',
        ]);

        $response = $this->actingAs($this->profesional)
            ->put(route('intervenciones.actualizar', $intervencion->id_intervencion), [
                'fecha_hora_intervencion' => $intervencion->fecha_hora_intervencion->format('Y-m-d'),
                'hora_intervencion'       => $intervencion->fecha_hora_intervencion->format('H:i:s'),
                'lugar'                   => 'Lugar actualizado',
                'modalidad'               => $intervencion->modalidad->value,
                'compromisos'             => 'Sin compromisos',
                'fk_id_profesional_generador' => $this->profesional->id_profesional,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('intervenciones', [
            'id_intervencion' => $intervencion->id_intervencion,
            'lugar'           => 'Lugar actualizado',
        ]);
    }

    #[Test]
    public function puede_eliminar_una_intervencion()
    {
        $intervencion = Intervencion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->delete(route('intervenciones.eliminar', $intervencion->id_intervencion));

        $response->assertRedirect();
        $this->assertDatabaseMissing('intervenciones', [
            'id_intervencion' => $intervencion->id_intervencion,
        ]);
    }

    #[Test]
    public function puede_cambiar_activo_de_intervencion()
    {
        $intervencion = Intervencion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->profesional)
            ->put(route('intervenciones.cambiarActivo', $intervencion->id_intervencion));

        $response->assertRedirect();
        $intervencion->refresh();
        $this->assertFalse($intervencion->activo);
    }

    #[Test]
    public function intervencion_pertenece_a_un_plan_de_accion()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $intervencion = Intervencion::factory()->create([
            'fk_id_plan_de_accion'        => $plan->id_plan_de_accion,
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $this->assertEquals($plan->id_plan_de_accion, $intervencion->planDeAccion->id_plan_de_accion);
    }

    #[Test]
    public function intervencion_puede_tener_multiples_alumnos()
    {
        $aula = Aula::inRandomOrder()->first();
        $alumnos = Alumno::factory()->count(3)->create(['fk_id_aula' => $aula->id_aula]);

        $intervencion = Intervencion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);
        $intervencion->alumnos()->sync($alumnos->pluck('id_alumno'));

        $this->assertCount(3, $intervencion->alumnos);
    }
}
