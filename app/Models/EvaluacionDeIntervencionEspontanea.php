<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDeIntervencionEspontanea extends Model
{
    protected $table = 'evaluaciones_intervenciones_espontaneas';
    protected $primaryKey = 'id_evaluacion_intervencion_espontanea';

    const CREATED_AT = 'fecha_hora';
    
    protected $fillable = [
        'fecha_hora', // 
        'observaciones',
        'criterios',
        'conclusiones',
    ];
    
    // revisado
    public function intervencionesEspontaneas():hasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }

    // revisado
    public function documentos(): hasMany{
        return $this->hasMany(Documento::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }
}
