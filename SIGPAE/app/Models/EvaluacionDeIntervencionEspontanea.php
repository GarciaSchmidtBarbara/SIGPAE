<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDeIntervencionEspontanea extends Model
{
    protected $table = 'evaluaciones_intervenciones_espontaneas';
    protected $primaryKey = 'id_evaluacion_intervencion_espontanea';
    protected $fillable = [
        'fecha_evaluacion',
        'observaciones',
        'criterios',
        'conclusiones',
    ];
    
    public function intervencionEspontanea()
    {
        return $this->hasmany(IntervencionEspontanea::class, 'fk_id_intervencion_espontanea', 'id_intervencion_espontanea');
    }
}
