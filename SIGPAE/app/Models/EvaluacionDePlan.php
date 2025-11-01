<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDePlan extends Model
{
    protected $table = 'evaluaciones_planes';
    protected $primaryKey = 'id_evaluacion_plan';
    protected $fillable = [
        'fecha_evaluacion',
        'observaciones',
        'criterios',
        'conclusiones',
    ];
    
    public function plan()
    {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan', 'id_plan');
    }   
}
