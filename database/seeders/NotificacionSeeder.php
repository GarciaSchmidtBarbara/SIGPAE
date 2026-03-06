<?php

namespace Database\Seeders;

use App\Enums\TipoEvento;
use App\Enums\TipoNotificacion;
use App\Models\Evento;
use App\Models\Intervencion;
use App\Models\Notificacion;
use App\Models\PlanDeAccion;
use App\Models\Profesional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $profesionales = Profesional::with('persona')->get();
        $destPrincipal = $profesionales->firstWhere('usuario', 'lucia.g');
        $origen        = $profesionales->first(fn($p) => $p->usuario !== 'lucia.g') ?? $profesionales->skip(1)->first();
        $origen2       = $profesionales->where('usuario', '!=', 'lucia.g')->skip(1)->first() ?? $origen;

        if (! $destPrincipal || ! $origen) {
            return;
        }

        $evento            = Evento::first();
        $evento2           = Evento::skip(1)->first() ?? $evento;
        $plan              = PlanDeAccion::first();
        $intervencion      = Intervencion::first();
        $derivacionVencida = Evento::where('tipo_evento', TipoEvento::DERIVACION_EXTERNA)
                                   ->where('lugar', 'Hospital Regional')
                                   ->first();

        $notificaciones = [
            [
                'tipo'    => TipoNotificacion::CONFIRMACION_ASISTENCIA,
                'mensaje' => ($origen->persona?->nombre ?? 'Un profesional') . ' confirmó su asistencia al evento del ' .
                             ($evento?->fecha_hora?->format('d/m/Y') ?? 'próximo turno') . '.',
                'leida'                => false,
                'fk_id_evento'         => $evento?->id_evento,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subMinutes(5),
            ],
            [
                'tipo'    => TipoNotificacion::CANCELACION_ASISTENCIA,
                'mensaje' => ($origen2->persona?->nombre ?? 'Un profesional') . ' canceló su asistencia al evento del ' .
                             ($evento?->fecha_hora?->format('d/m/Y') ?? 'próximo turno') . '.',
                'leida'                => false,
                'fk_id_evento'         => $evento?->id_evento,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subMinutes(30),
            ],
            [
                'tipo'    => TipoNotificacion::EVENTO_EDITADO,
                'mensaje' => 'El evento del ' . ($evento2?->fecha_hora?->format('d/m/Y') ?? 'próxima fecha') .
                             ' fue modificado por ' . ($origen->persona?->nombre ?? 'Un profesional') . '.',
                'leida'                => false,
                'fk_id_evento'         => $evento2?->id_evento,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subHours(1),
            ],
            [
                'tipo'    => TipoNotificacion::EVENTO_BORRADO,
                'mensaje' => "El evento en 'Sala de reuniones' del 15/02/2026 fue eliminado por " .
                             ($origen->persona?->nombre ?? 'Un profesional') . '.',
                'leida'                => false,
                'fk_id_evento'         => null,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subHours(3),
            ],
            [
                'tipo'    => TipoNotificacion::PLAN_EDITADO,
                'mensaje' => 'El Plan de Acción #' . ($plan?->id_plan_de_accion ?? '1') .
                             ' fue modificado por ' . ($origen->persona?->nombre ?? 'Un profesional') . '.',
                'leida'                => true,
                'fk_id_evento'         => null,
                'fk_id_plan_de_accion' => $plan?->id_plan_de_accion,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subDays(1),
            ],
            [
                'tipo'    => TipoNotificacion::PLAN_BORRADO,
                'mensaje' => 'El Plan de Acción #99 fue eliminado por ' .
                             ($origen->persona?->nombre ?? 'Un profesional') . '.',
                'leida'                => true,
                'fk_id_evento'         => null,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subDays(2),
            ],
            [
                'tipo'    => TipoNotificacion::INTERVENCION_EDITADA,
                'mensaje' => 'La intervención del ' .
                             ($intervencion?->fecha_hora_intervencion?->format('d/m/Y') ?? '10/01/2026') .
                             ' fue modificada por ' . ($origen->persona?->nombre ?? 'Un profesional') . '.',
                'leida'                => true,
                'fk_id_evento'         => null,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => $intervencion?->id_intervencion,
                'offset'               => now()->subDays(3),
            ],
            [
                'tipo'    => TipoNotificacion::INTERVENCION_BORRADA,
                'mensaje' => 'La intervención del 05/01/2026 fue eliminada por ' .
                             ($origen->persona?->nombre ?? 'Un profesional') . '.',
                'leida'                => true,
                'fk_id_evento'         => null,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'offset'               => now()->subDays(5),
            ],
            [
                'tipo'    => TipoNotificacion::RECORDATORIO_DERIVACION,
                'mensaje' => 'Recordatorio: hay una derivación externa pendiente (Hospital Regional – Dr. Ramírez). Han pasado 1 semana desde el último recordatorio.',
                'leida'                => false,
                'fk_id_evento'         => $derivacionVencida?->id_evento,
                'fk_id_plan_de_accion' => null,
                'fk_id_intervencion'   => null,
                'id_origen'            => null, // notificación del sistema, sin origen
                'offset'               => now()->subMinutes(1),
            ],
        ];

        foreach ($notificaciones as $n) {
            Notificacion::create([
                'tipo'                           => $n['tipo'],
                'mensaje'                        => $n['mensaje'],
                'leida'                          => $n['leida'],
                'fk_id_profesional_destinatario' => $destPrincipal->id_profesional,
                'fk_id_profesional_origen'       => array_key_exists('id_origen', $n) ? $n['id_origen'] : $origen->id_profesional,
                'fk_id_evento'                   => $n['fk_id_evento'],
                'fk_id_plan_de_accion'           => $n['fk_id_plan_de_accion'],
                'fk_id_intervencion'             => $n['fk_id_intervencion'],
                'created_at'                     => $n['offset'],
                'updated_at'                     => $n['offset'],
            ]);
        }

        // Invalidar cache de conteo de no leídas (el seeder bypasea el repositorio)
        Cache::forget("notif_no_leidas_{$destPrincipal->id_profesional}");
    }
}
