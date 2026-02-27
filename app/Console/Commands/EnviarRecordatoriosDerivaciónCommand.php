<?php

namespace App\Console\Commands;

use App\Enums\TipoNotificacion;
use App\Services\Interfaces\EventoServiceInterface;
use App\Services\Interfaces\NotificacionServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

//No se usa un observer porque estos reaccionan a eventos de creación/actualización/eliminación,
//y lo que queremos es enviar recordatorios periódicos para eventos ya existentes,
//no solo cuando se crean o actualizan. 
//Por eso es más adecuado usar un comando programado (scheduled command) 
//que se ejecute diariamente y revise qué eventos necesitan enviar recordatorios.

class EnviarRecordatoriosDerivaciónCommand extends Command
{
    protected $signature = 'enviar:recordatorios-derivacion';

    protected $description = 'Envía notificaciones de recordatorio para derivaciones externas pendientes';

    public function __construct(
        private EventoServiceInterface $eventoService,
        private NotificacionServiceInterface $notificacionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $eventos  = $this->eventoService->obtenerDerivacionesPendientesRecordatorio();
        $enviados = 0;

        foreach ($eventos as $evento) {
            $base    = $evento->ultimo_recordatorio_at ?? $evento->fecha_hora;
            $proximo = (clone $base)->addWeeks($evento->periodo_recordatorio);

            if (Carbon::now()->lt($proximo)) {
                continue;
            }

            $this->notificacionService->crear(
                tipo: TipoNotificacion::RECORDATORIO_DERIVACION,
                mensaje: "Recordatorio: hay una derivación externa pendiente. Han pasado {$evento->periodo_recordatorio} " .
                    ($evento->periodo_recordatorio === 1 ? 'semana' : 'semanas') . ' desde el último recordatorio.',
                idDestinatario: $evento->fk_id_profesional_creador,
                idOrigen: null,
                idEvento: $evento->id_evento,
                idPlan: null,
                idIntervencion: null,
            );

            $this->eventoService->actualizar($evento->id_evento, [
                'ultimo_recordatorio_at' => Carbon::now(),
            ]);

            $enviados++;
        }

        $this->info("Recordatorios enviados: {$enviados}");

        return Command::SUCCESS;
    }
}
