<?php

namespace App\Enums;

enum TipoPlan: string
{
    case INDIVIDUAL = 'INDIVIDUAL';
    case GRUPAL = 'GRUPAL';
    case INSTITUCIONAL = 'INSTITUCIONAL';
}