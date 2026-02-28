<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDePlan extends Model
{
    protected $table = 'evaluaciones_planes';
    protected $primaryKey = 'id_evaluacion_plan_de_accion';

    protected $fillable = [
        'observaciones',
        'criterios',
        'conclusiones',
        'tipo',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
    ];

    // revisado
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    // revisado
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'fk_id_evaluacion_plan_de_accion', 'id_evaluacion_plan_de_accion');
    }
}
