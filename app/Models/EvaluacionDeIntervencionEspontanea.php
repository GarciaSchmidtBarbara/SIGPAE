<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDeIntervencionEspontanea extends Model
{
    protected $table = 'evaluaciones_intervenciones_espontaneas';
    protected $primaryKey = 'id_evaluacion_intervencion_espontanea';
    
    protected $fillable = [
        'observaciones',
        'criterios',
        'conclusiones',
    ];
    
    public function intervencionesEspontaneas(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }

    public function documentos(): HasMany{
        return $this->hasMany(Documento::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }
}
