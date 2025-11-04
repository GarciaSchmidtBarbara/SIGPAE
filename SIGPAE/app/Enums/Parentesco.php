<?php

namespace App\Enums;

enum Parentesco: string
{
    case PADRE = 'PADRE';
    case MADRE = 'MADRE';
    case HERMANO = 'HERMANO';
    case TUTOR = 'TUTOR';
    case OTRO = 'OTRO';
}