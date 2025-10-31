<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    // nombre personalizado de la tabla asociada al modelo
    protected $table = 'evaluaciones';
    // clave primaria personalizada
    protected $primaryKey = 'id_evaluacion';

    const CREATED_AT = 'fecha_hora_creacion';
}
