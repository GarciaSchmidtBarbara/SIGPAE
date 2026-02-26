<?php

namespace App\Enums;

enum TipoNotificacion: string
{
    //Eventos: 
    //El creador es notificado cuando un invitado cambia su confirmación
    case CONFIRMACION_ASISTENCIA  = 'CONFIRMACION_ASISTENCIA';
    case CANCELACION_ASISTENCIA   = 'CANCELACION_ASISTENCIA';

    //El invitado es notificado cuando el evento al que fue invitado cambia
    case EVENTO_EDITADO           = 'EVENTO_EDITADO';
    case EVENTO_BORRADO           = 'EVENTO_BORRADO';

    //Planes de acción: 
    //Participantes notificados cuando el plan cambia
    case PLAN_EDITADO             = 'PLAN_EDITADO';
    case PLAN_BORRADO             = 'PLAN_BORRADO';

    //Intervenciones: 
    //Profesionales relacionados notificados cuando la intervención cambia
    case INTERVENCION_EDITADA     = 'INTERVENCION_EDITADA';
    case INTERVENCION_BORRADA     = 'INTERVENCION_BORRADA';

    public function etiqueta(): string
    {
        return match($this) {
            self::CONFIRMACION_ASISTENCIA => 'Confirmó asistencia',
            self::CANCELACION_ASISTENCIA  => 'Canceló asistencia',
            self::EVENTO_EDITADO          => 'Evento editado',
            self::EVENTO_BORRADO          => 'Evento eliminado',
            self::PLAN_EDITADO            => 'Plan de acción editado',
            self::PLAN_BORRADO            => 'Plan de acción eliminado',
            self::INTERVENCION_EDITADA    => 'Intervención editada',
            self::INTERVENCION_BORRADA    => 'Intervención eliminada',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::CONFIRMACION_ASISTENCIA => 'fa-check-circle',
            self::CANCELACION_ASISTENCIA  => 'fa-times-circle',
            self::EVENTO_EDITADO          => 'fa-pencil-alt',
            self::EVENTO_BORRADO          => 'fa-trash-alt',
            self::PLAN_EDITADO            => 'fa-pencil-alt',
            self::PLAN_BORRADO            => 'fa-trash-alt',
            self::INTERVENCION_EDITADA    => 'fa-pencil-alt',
            self::INTERVENCION_BORRADA    => 'fa-trash-alt',
        };
    }

    public function iconoColor(): string
    {
        return match($this) {
            self::CONFIRMACION_ASISTENCIA => 'text-green-500',
            self::CANCELACION_ASISTENCIA  => 'text-red-500',
            self::EVENTO_EDITADO          => 'text-yellow-500',
            self::EVENTO_BORRADO          => 'text-red-500',
            self::PLAN_EDITADO            => 'text-yellow-500',
            self::PLAN_BORRADO            => 'text-red-500',
            self::INTERVENCION_EDITADA    => 'text-yellow-500',
            self::INTERVENCION_BORRADA    => 'text-red-500',
        };
    }
}
