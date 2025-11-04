<?php

namespace App\Enums;

enum TipoFormato: string
{
    case DOCX = 'DOCX';
    case DOC = 'DOC';
    case JPG = 'JPG';
    case PNG = 'PNG';
    case PDF = 'PDF';
    case XLS = 'XLS';
    case XLSX = 'XLSX';
}