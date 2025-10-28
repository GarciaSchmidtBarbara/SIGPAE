<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    // clave primaria personalizada
    protected $primaryKey = 'id_documento';

    const CREATED_AT = 'fecha_hora_carga';
}
