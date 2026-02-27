<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Aula;
use App\Models\Alumno;
use App\Models\PlanDeAccion;
use App\Models\Intervencion;
use App\Models\Evento;
use App\Models\Documento;
use Illuminate\Support\Facades\Hash;
use App\Models\Notificacion;
use App\Enums\TipoNotificacion;
use App\Enums\TipoEvento;


class BaseInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::firstOrCreate(
            ['dni' => '12345678'], // condición para buscar
            [ // datos para crear si no existe
            'nombre' => 'Lucía',
            'apellido' => 'González',
            'fecha_nacimiento' => '1990-05-12',
            'domicilio' => 'Av. San Martín 123',
            'nacionalidad' => 'Argentina',
        ]);
        Profesional::firstOrCreate(
            ['usuario' => 'lucia.g'], // condición para buscar
            [
            'profesion' => 'Psicopedagoga',
            'siglas' => 'PS',
            'telefono' => '2901-123456',
            'email' => 'lucia@example.com',
            'contrasenia' => Hash::make('segura123'),
            'fk_id_persona' => $persona->id_persona,
        ]);

        Profesional::factory()->count(3)->create();

        
        \App\Models\Aula::factory()->count(10)->create();
        

        \App\Models\Alumno::factory()->count(10)->create();
        
        
        // Crear Planes Individuales (con 1 Alumno y 1 profesional participante)
        PlanDeAccion::factory()->count(5)->individual()->create();

        // Crear Planes Grupales (con Aulas y múltiples Alumnos/Responsables)
        PlanDeAccion::factory()->count(6)->grupal()->create();

        // Crear Planes Institucionales (con múltiples responsables)
        PlanDeAccion::factory()->count(2)->institucional()->create();

        // Crear Intervenciones
        Intervencion::factory()->count(10)->create();

        // Crear Eventos variados
        // Eventos BANDA (reuniones de grupo)
        Evento::factory()->count(3)->banda()->create();
        
        // Eventos de Reunión de Gabinete
        Evento::factory()->count(2)->reunionGabinete()->create();
        
        // Eventos de Reunión Derivación
        Evento::factory()->count(2)->reunionDerivacion()->create();
        
        // Eventos de Cita Familiar
        Evento::factory()->count(4)->citaFamiliar()->create();
        
        // Eventos de Derivación Externa (sin profesionales invitados)
        Evento::factory()->count(3)->derivacionExterna()->create();
        
        // Algunos eventos adicionales aleatorios
        Evento::factory()->count(5)->create();
        
        // Algunos eventos en el pasado
        Evento::factory()->count(3)->pasado()->create();
        
        // Algunos eventos futuros
        Evento::factory()->count(3)->futuro()->create();

        //Notificaciones
        $profesionales = Profesional::all();
        $destPrincipal = $profesionales->firstWhere('usuario', 'lucia.g');
        $origen        = $profesionales->first(fn($p) => $p->usuario !== 'lucia.g') ?? $profesionales->skip(1)->first();
        $origen2       = $profesionales->where('usuario', '!=', 'lucia.g')->skip(1)->first() ?? $origen;

        $evento       = Evento::first();
        $evento2      = Evento::skip(1)->first() ?? $evento;
        $plan         = PlanDeAccion::first();
        $intervencion = Intervencion::first();

        //Derivación externa vencida: fecha_hora hace 2 semanas, periodo = 1 semana
        //el comando la detectaría como pendiente hoy; la notificación se genera directo en el seeder
        $derivacionVencida = null;
        if ($destPrincipal) {
            $derivacionVencida = Evento::create([
                'tipo_evento'           => TipoEvento::DERIVACION_EXTERNA,
                'fecha_hora'            => now()->subWeeks(2),
                'lugar'                 => 'Hospital Regional',
                'notas'                 => 'Profesional externo: Dr. Ramírez (neurología)',
                'profesional_tratante'  => 'Dr. Ramírez',
                'periodo_recordatorio'  => 1,
                'ultimo_recordatorio_at' => null,
                'fk_id_profesional_creador' => $destPrincipal->id_profesional,
            ]);
        }

        if ($destPrincipal && $origen) {
            $notificaciones = [
                // NO leídas
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
                // Leídas
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
                // Recordatorio de derivación externa vencida
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
        } // fin if ($destPrincipal && $origen)

        // ── Documentos de ejemplo ───────────────────────────────────────
        $profDoc = Profesional::first();

        // Institucionales
        Documento::factory()->count(3)->institucional()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();

        // Perfil de alumno
        Documento::factory()->count(3)->perfilAlumno()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();

        // Plan de acción
        Documento::factory()->count(2)->planAccion()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();

        // Intervención
        Documento::factory()->count(2)->intervencion()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();
    }
}
