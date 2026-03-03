<?php

namespace Database\Seeders;

use App\Enums\TipoEvento;
use App\Models\Evento;
use App\Models\Profesional;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    public function run(): void
    {
        Evento::factory()->count(3)->banda()->create();

        Evento::factory()->count(2)->reunionGabinete()->create();

        Evento::factory()->count(2)->reunionDerivacion()->create();

        Evento::factory()->count(4)->citaFamiliar()->create();

        Evento::factory()->count(3)->derivacionExterna()->create();

        Evento::factory()->count(5)->create();

        Evento::factory()->count(3)->pasado()->create();

        Evento::factory()->count(3)->futuro()->create();

        //Derivación externa vencida: fecha_hora hace 2 semanas, periodo = 1 semana
        //El comando la detectaría como pendiente hoy; la notificación se genera en NotificacionSeeder
        $destPrincipal = Profesional::firstWhere('usuario', 'lucia.g');

        if ($destPrincipal) {
            Evento::create([
                'tipo_evento'               => TipoEvento::DERIVACION_EXTERNA,
                'fecha_hora'                => now()->subWeeks(2),
                'lugar'                     => 'Hospital Regional',
                'notas'                     => 'Profesional externo: Dr. Ramírez (neurología)',
                'profesional_tratante'      => 'Dr. Ramírez',
                'periodo_recordatorio'      => 1,
                'ultimo_recordatorio_at'    => null,
                'fk_id_profesional_creador' => $destPrincipal->id_profesional,
            ]);
        }
    }
}
