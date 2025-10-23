<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentacion extends Model
{
    // clave primaria personalizada
    protected $primaryKey = 'id_documentacion';

    const CREATED_AT = 'fecha_hora_carga';
}
