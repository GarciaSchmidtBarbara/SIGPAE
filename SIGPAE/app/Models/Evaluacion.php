<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    // clave primaria personalizada
    protected $primaryKey = 'id_evaluacion';

    const CREATED_AT = 'fecha_hora_creacion';
}
