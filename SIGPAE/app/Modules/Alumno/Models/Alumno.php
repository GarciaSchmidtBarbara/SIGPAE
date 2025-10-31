<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;

class Alumno extends Model
{
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'inasistencias',
        'cud',
        'fk_id_persona',
        'fk_id_aula',
    ];

    public function getDescripcionAttribute(){
        return $this->nombre . ' ' . $this->apellido;
    }

    public function persona(): BelongsTo {
        return $this->belongsTo(Persona::class, 'fk_id_persona');
    }

    public function familiares(): BelongsToMany{        
        return $this->belongsToMany(Familiar::class, 'alumno_familiar', 'id_alumno', 'id_familiar');
    }

    public function hermanos(): BelongsToMany{
        return $this->belongsToMany(Hermano::class, 'alumno_hermano', 'id_alumno', 'id_hermano');
    }

    public function eventos(): belongsToMany{
        return $this->belongsToMany(Evento::class, 'evento_alumno', 'id_alumno', 'id_evento');
    }

    public function intervenciones(): BelongsToMany{
        return $this->belongsToMany(Intervencion::class, 'intervencion_alumno', 'fk_id_alumno', 'fk_id_intervencion');
    }

    public function planesDeAccion(): BelongsToMany{
        return $this->belongsToMany(PlanDeAccion::class, 'plan_alumno', 'fk_id_alumno', 'fk_id_plan');
    }
}