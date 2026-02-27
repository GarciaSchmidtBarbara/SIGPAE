<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TipoNotificacion;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';

    protected $fillable = [
        'tipo',
        'mensaje',
        'leida',
        'fk_id_evento',
        'fk_id_plan_de_accion',
        'fk_id_intervencion',
        'fk_id_profesional_destinatario',
        'fk_id_profesional_origen',
    ];

    protected $casts = [
        'tipo'   => TipoNotificacion::class,
        'leida'  => 'boolean',
    ];


    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_destinatario', 'id_profesional');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_origen', 'id_profesional');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'fk_id_evento', 'id_evento');
    }

    public function planDeAccion(): BelongsTo
    {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(Intervencion::class, 'fk_id_intervencion', 'id_intervencion');
    }


//Las notificaciones de tipo de borrado no tienen destino (no existe más el recurso)
//para el resto se hacen un url
    public function urlDestino(): ?string
    {
        if ($this->recursoBorrado()) {
            return null;
        }

        if ($this->fk_id_evento && $this->evento) {
            if ($this->evento->tipo_evento === \App\Enums\TipoEvento::DERIVACION_EXTERNA) {
                return route('eventos.editar-derivacion', $this->fk_id_evento);
            }
            return route('eventos.ver', $this->fk_id_evento);
        }
        if ($this->fk_id_plan_de_accion && $this->planDeAccion) {
            return route('planDeAccion.iniciar-edicion', $this->fk_id_plan_de_accion);
        }
        if ($this->fk_id_intervencion && $this->intervencion) {
            return route('intervenciones.editar', $this->fk_id_intervencion);
        }
        return null;
    }

//Se usa el enum para ver si fue borrado
    public function recursoBorrado(): bool
    {
        return in_array($this->tipo, [
            TipoNotificacion::EVENTO_BORRADO,
            TipoNotificacion::PLAN_BORRADO,
            TipoNotificacion::INTERVENCION_BORRADA,
        ]);
    }
}
