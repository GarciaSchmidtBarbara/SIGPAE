<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDePlan extends Model
{
    protected $table = 'evaluaciones_planes';
    protected $primaryKey = 'id_evaluacion_plan_de_accion';

    const CREATED_AT = 'fecha_hora';

    protected $fillable = [
        'fecha_hora',
        'observaciones',
        'criterios',
        'conclusiones',
    ];
    
    protected $casts = [
        'fecha_hora' => 'datetime',
        'observaciones' => 'string', // será tratado como text
        'criterios' => 'string', // será tratado como text
        'conclusiones' => 'string', // será tratado como text
    ];

    // revisado
    public function plan(): belongsTo
    {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    // revisado
    public function documentos(): hasMany{
        return $this->hasMany(Documento::class, 'fk_id_evaluacion_plan_de_accion', 'id_evaluacion_plan_de_accion');
    }
}
