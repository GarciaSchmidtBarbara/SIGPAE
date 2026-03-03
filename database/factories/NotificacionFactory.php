<?php

namespace Database\Factories;

use App\Enums\TipoNotificacion;
use App\Models\Notificacion;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    public function definition(): array
    {
        $tipos = [
            TipoNotificacion::CONFIRMACION_ASISTENCIA,
            TipoNotificacion::CANCELACION_ASISTENCIA,
            TipoNotificacion::EVENTO_EDITADO,
            TipoNotificacion::PLAN_EDITADO,
            TipoNotificacion::INTERVENCION_EDITADA,
        ];

        return [
            'tipo'                           => $this->faker->randomElement($tipos),
            'mensaje'                        => $this->faker->sentence(),
            'leida'                          => false,
            'fk_id_profesional_destinatario' => Profesional::factory(),
            'fk_id_profesional_origen'       => Profesional::factory(),
            'fk_id_evento'                   => null,
            'fk_id_plan_de_accion'           => null,
            'fk_id_intervencion'             => null,
        ];
    }

    public function leida(): static
    {
        return $this->state(['leida' => true]);
    }

    public function noLeida(): static
    {
        return $this->state(['leida' => false]);
    }

    public function borrado(): static
    {
        return $this->state([
            'tipo'         => $this->faker->randomElement([
                TipoNotificacion::EVENTO_BORRADO,
                TipoNotificacion::PLAN_BORRADO,
                TipoNotificacion::INTERVENCION_BORRADA,
            ]),
            'fk_id_evento'         => null,
            'fk_id_plan_de_accion' => null,
            'fk_id_intervencion'   => null,
        ]);
    }
}
