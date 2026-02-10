<?php

namespace App\Enums;

enum Siglas: string
{
    case AS = 'AS';
    case AT = 'AT';
    case FN = 'FN';
    case PG = 'PG';
    case PS = 'PS';

    public function label(): string
    {
        return match($this) {
            self::AS => 'Asistente Social',
            self::AT => 'Acompañante Terapéutico',
            self::FN => 'Fonoaudiólogo',
            self::PG => 'Psicopedagogo',
            self::PS => 'Psicólogo',
        };
    }
}
