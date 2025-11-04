<?php

namespace App\Enums;

enum TipoEvento: string
{
    case BANDA = 'BANDA';
    case RG = 'RG';
    case RD = 'RD';
    case CITA_FAMILIAR = 'CITA_FAMILIAR';
}