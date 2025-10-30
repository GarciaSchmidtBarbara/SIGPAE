<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDeIntervencionEspontanea extends Model
{
    // nombre personalizado de la tabla asociada al modelo
    protected $table = 'evaluaciones_de_intervencion_espontanea';
    // clave primaria personalizada
    protected $primaryKey = 'id_evaluaciones_de_intervencion_espontanea';
}
