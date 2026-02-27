<?php

namespace Database\Factories;

use App\Enums\TipoFormato;
use App\Models\Alumno;
use App\Models\Documento;
use App\Models\Intervencion;
use App\Models\PlanDeAccion;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoFactory extends Factory
{
    protected $model = Documento::class;

    // Archivos de muestra (simplemente placeholder paths, no archivos reales)
    private static array $nombrePlaceholders = [
        'Autorización familiar',
        'Informe psicopedagógico',
        'Evaluación de desempeño',
        'Acuerdo de convivencia',
        'Certificado médico',
        'Informe de derivación',
        'Diagnóstico inicial',
        'Plan de trabajo trimestral',
        'Acta de reunión',
        'Nota informativa',
        'Consentimiento informado',
        'Registro de asistencia',
        'Cronograma de actividades',
    ];

    public function definition(): array
    {
        $formatos = TipoFormato::cases();
        $formato  = $this->faker->randomElement($formatos);

        return [
            'nombre'                => $this->faker->unique()->randomElement(self::$nombrePlaceholders)
                                       . ' - ' . $this->faker->lastName(),
            'contexto'              => 'institucional',
            'tipo_formato'          => $formato->value,
            'disponible_presencial' => $this->faker->boolean(),
            'ruta_archivo'          => 'documentos/placeholder_' . $this->faker->uuid() . '.' . strtolower($formato->value),
            'tamanio_archivo'       => $this->faker->numberBetween(50_000, 5_000_000),
            'fk_id_profesional'     => null,
        ];
    }

    // ── Estado: Perfil de alumno ──────────────────────────────

    public function perfilAlumno(): static
    {
        return $this->state(function () {
            $alumno = Alumno::inRandomOrder()->first();
            return [
                'contexto'      => 'perfil_alumno',
                'fk_id_alumno'  => $alumno?->id_alumno,
            ];
        });
    }

    // ── Estado: Plan de acción ────────────────────────────────

    public function planAccion(): static
    {
        return $this->state(function () {
            $plan = PlanDeAccion::inRandomOrder()->first();
            return [
                'contexto'            => 'plan_accion',
                'fk_id_plan_de_accion' => $plan?->id_plan_de_accion,
            ];
        });
    }

    // ── Estado: Intervención ──────────────────────────────────

    public function intervencion(): static
    {
        return $this->state(function () {
            $intervencion = Intervencion::inRandomOrder()->first();
            return [
                'contexto'          => 'intervencion',
                'fk_id_intervencion' => $intervencion?->id_intervencion,
            ];
        });
    }

    // ── Estado: Institucional ─────────────────────────────────

    public function institucional(): static
    {
        return $this->state(['contexto' => 'institucional']);
    }
}
