<?php

namespace App\Enums;

enum Modalidad: string
{
    case PRESENCIAL = 'PRESENCIAL';
    case ONLINE = 'ONLINE';
    case OTRA = 'OTRA';
}