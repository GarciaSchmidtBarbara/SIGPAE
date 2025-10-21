<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;

class Intervencion extends Model
{
    protected $table = 'intervenciones';

    protected $primaryKey = 'id_intervencion';

    protected $fillable = [
        'fecha',
        'lugar',
        'tipo',
        'fk_profesional_creador',
        'fk_id_plan',
    ];

    public function getDescripcionAttribute(){
        return $this->lugar . ' ' . $this->fecha . ' ' . $this->tipo;
    }

    public function planDeAccion(): BelongsTo {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan');
    }

    public function profesionalCreador(): BelongsTo {
        return $this->belongsTo(Profesional::class, 'fk_profesional_creador');
    }

    public function aulas(): BelongsToMany{
        return $this->belongsToMany(Aula::class, 'intervencion_aula', 'fk_id_intervencion', 'fk_id_aula');
    }
    
    public function alumnos(): BelongsToMany{
        return $this->belongsToMany(Alumno::class, 'intervencion_alumno', 'fk_id_intervencion', 'fk_id_alumno');
    }

    public function evaluacion(): BelongsTo{
        return $this->belongsTo(Evaluacion::class, 'fk_id_evaluacion');
    }

    public function documentaciones(): BelongsToMany{
        return $this->belongsToMany(Documentacion::class, 'documentacion_intervencion', 'fk_id_intervencion', 'fk_id_documentacion');
    }

  

}