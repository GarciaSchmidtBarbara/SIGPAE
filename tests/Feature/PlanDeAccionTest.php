<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\EvaluacionDePlan;
use App\Models\Intervencion;
use App\Models\Persona;
use App\Models\PlanDeAccion;
use App\Models\Profesional;
use App\Enums\TipoPlan;
use App\Enums\EstadoPlan;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Hash;
use App\Services\Interfaces\NotificacionServiceInterface;

class PlanDeAccionTest extends TestCase
{
    use DatabaseTruncation;

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

    #[Test]
    public function usuario_autenticado_puede_ver_lista_de_planes()
    {
        PlanDeAccion::factory()->count(3)->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('planDeAccion.principal'));

        $response->assertStatus(200);
        $response->assertViewIs('planDeAccion.principal');
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_planes()
    {
        $response = $this->get(route('planDeAccion.principal'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function puede_ver_formulario_de_creacion()
    {
        $response = $this->actingAs($this->profesional)
            ->get(route('planDeAccion.iniciar-creacion'));

        $response->assertStatus(200);
        $response->assertViewIs('planDeAccion.crear-editar');
    }

    #[Test]
    public function puede_crear_plan_individual()
    {
        $aula = Aula::first();
        $alumno = Alumno::factory()->create(['fk_id_aula' => $aula->id_aula]);

        $datos = [
            'tipo_plan'      => TipoPlan::INDIVIDUAL->value,
            'objetivos'      => 'Mejorar rendimiento académico',
            'acciones'       => 'Seguimiento semanal',
            'observaciones'  => 'Alumno con potencial',
            'alumnos'        => [$alumno->id_alumno],
            'profesionales'  => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.store'), $datos);

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('planes_de_accion', [
            'tipo_plan'  => TipoPlan::INDIVIDUAL->value,
            'objetivos'  => 'Mejorar rendimiento académico',
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);
    }

    #[Test]
    public function puede_crear_plan_grupal_con_aula()
    {
        $aula = Aula::first();

        $datos = [
            'tipo_plan'     => TipoPlan::GRUPAL->value,
            'objetivos'     => 'Mejorar convivencia',
            'acciones'      => 'Talleres grupales',
            'aula'          => $aula->id_aula,
            'alumnos'       => [],
            'profesionales' => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.store'), $datos);

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('planes_de_accion', [
            'tipo_plan' => TipoPlan::GRUPAL->value,
            'objetivos' => 'Mejorar convivencia',
        ]);
    }

    #[Test]
    public function puede_crear_plan_institucional()
    {
        $datos = [
            'tipo_plan'     => TipoPlan::INSTITUCIONAL->value,
            'objetivos'     => 'Plan institucional anual',
            'acciones'      => 'Capacitaciones docentes',
            'profesionales' => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.store'), $datos);

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('planes_de_accion', [
            'tipo_plan' => TipoPlan::INSTITUCIONAL->value,
        ]);
    }

    #[Test]
    public function no_puede_crear_plan_individual_sin_alumnos()
    {
        $datos = [
            'tipo_plan'     => TipoPlan::INDIVIDUAL->value,
            'objetivos'     => 'Objetivos',
            'acciones'      => 'Acciones',
            'alumnos'       => [],
            'profesionales' => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.store'), $datos);

        $response->assertSessionHasErrors(['alumnos']);
    }

    #[Test]
    public function no_puede_crear_plan_sin_tipo()
    {
        $datos = [
            'objetivos' => 'Objetivos',
            'acciones'  => 'Acciones',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.store'), $datos);

        $response->assertSessionHasErrors(['tipo_plan']);
    }

    #[Test]
    public function no_puede_crear_plan_sin_objetivos()
    {
        $datos = [
            'tipo_plan'     => TipoPlan::INSTITUCIONAL->value,
            'acciones'      => 'Acciones',
            'profesionales' => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.store'), $datos);

        $response->assertSessionHasErrors(['objetivos']);
    }

    #[Test]
    public function puede_ver_formulario_de_edicion()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('planDeAccion.iniciar-edicion', $plan->id_plan_de_accion));

        $response->assertStatus(200);
        $response->assertViewIs('planDeAccion.crear-editar');
    }

    #[Test]
    public function puede_actualizar_plan_existente()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'objetivos' => 'Objetivos originales',
        ]);

        $datosActualizados = [
            'tipo_plan'     => $plan->tipo_plan->value,
            'objetivos'     => 'Objetivos actualizados',
            'acciones'      => 'Acciones actualizadas',
            'profesionales' => [$this->profesional->id_profesional],
        ];

        $response = $this->actingAs($this->profesional)
            ->put(route('planDeAccion.actualizar', $plan->id_plan_de_accion), $datosActualizados);

        $response->assertRedirect(route('planDeAccion.principal'));

        $this->assertDatabaseHas('planes_de_accion', [
            'id_plan_de_accion' => $plan->id_plan_de_accion,
            'objetivos'         => 'Objetivos actualizados',
        ]);
    }

    #[Test]
    public function puede_eliminar_plan_sin_intervenciones()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->delete(route('planDeAccion.eliminar', $plan->id_plan_de_accion));

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('success');

        // Soft delete: el registro sigue pero con deleted_at
        $this->assertSoftDeleted('planes_de_accion', [
            'id_plan_de_accion' => $plan->id_plan_de_accion,
        ]);
    }

    #[Test]
    public function no_puede_eliminar_plan_con_intervenciones()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        Intervencion::factory()->create([
            'fk_id_plan_de_accion'        => $plan->id_plan_de_accion,
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);

        $response = $this->actingAs($this->profesional)
            ->delete(route('planDeAccion.eliminar', $plan->id_plan_de_accion));

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('error');

        // El plan NO debe haberse eliminado
        $this->assertDatabaseHas('planes_de_accion', [
            'id_plan_de_accion' => $plan->id_plan_de_accion,
        ]);
    }

    #[Test]
    public function puede_ver_papelera()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);
        $plan->delete(); // Soft delete

        $response = $this->actingAs($this->profesional)
            ->get(route('planDeAccion.papelera'));

        $response->assertStatus(200);
        $response->assertViewIs('planDeAccion.papelera');
    }

    #[Test]
    public function puede_restaurar_plan_eliminado()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);
        $plan->delete();

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.restaurar', $plan->id_plan_de_accion));

        $response->assertRedirect(route('planDeAccion.papelera'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('planes_de_accion', [
            'id_plan_de_accion' => $plan->id_plan_de_accion,
            'deleted_at'        => null,
        ]);
    }

    #[Test]
    public function puede_eliminar_plan_definitivamente()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
        ]);
        $plan->delete(); // Primero soft delete

        $response = $this->actingAs($this->profesional)
            ->delete(route('planDeAccion.destruir', $plan->id_plan_de_accion));

        $response->assertRedirect(route('planDeAccion.papelera'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('planes_de_accion', [
            'id_plan_de_accion' => $plan->id_plan_de_accion,
        ]);
    }

    #[Test]
    public function puede_cambiar_estado_activo_de_plan()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->profesional)
            ->patch(route('planDeAccion.cambiarActivo', $plan->id_plan_de_accion));

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_filtrar_planes_por_estado()
    {
        PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'activo' => true,
            'estado_plan' => EstadoPlan::ABIERTO,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('planDeAccion.principal', ['estado' => 'activos']));

        $response->assertStatus(200);
        $response->assertViewIs('planDeAccion.principal');
    }

    #[Test]
    public function puede_guardar_evaluacion_de_plan()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'activo' => true,
            'estado_plan' => EstadoPlan::ABIERTO,
        ]);

        $datos = [
            'criterios'     => 'Criterios de evaluación',
            'observaciones' => 'Observaciones de la evaluación',
            'conclusiones'  => 'Conclusiones finales',
        ];

        $response = $this->actingAs($this->profesional)
            ->post(route('planDeAccion.guardarEvaluacion', $plan->id_plan_de_accion), $datos);

        $response->assertRedirect(route('planDeAccion.principal'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_ver_formulario_de_evaluacion()
    {
        $plan = PlanDeAccion::factory()->create([
            'fk_id_profesional_generador' => $this->profesional->id_profesional,
            'activo' => true,
            'estado_plan' => EstadoPlan::ABIERTO,
        ]);

        $response = $this->actingAs($this->profesional)
            ->get(route('planDeAccion.crearEvaluacion', $plan->id_plan_de_accion));

        $response->assertStatus(200);
        $response->assertViewIs('planDeAccion.evaluacion');
    }
}
