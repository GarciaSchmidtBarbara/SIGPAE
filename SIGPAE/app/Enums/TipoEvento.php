<?php

namespace App\Enums;

enum TipoEvento: string
{
    case BANDA = 'BANDA';
    case RG = 'RG';
    case RD = 'RD';
    case CITA_FAMILIAR = 'CITA_FAMILIAR';
    case DERIVACION_EXTERNA = 'DERIVACION_EXTERNA';

    public function label(): string
    {
        return match ($this) {
            self::BANDA => 'Banda',
            self::RG => 'RG (Reunión General)',
            self::RD => 'RD (Reunión de Derivación)',
            self::CITA_FAMILIAR => 'Cita Familiar',
            self::DERIVACION_EXTERNA => 'Derivación Externa',
        };
    }
}