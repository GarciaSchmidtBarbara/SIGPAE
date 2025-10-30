<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;

class Alumno extends Model
{
    protected $table = 'alumnos';

    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'cud',
        'inasistencias',
        'observaciones',
        'antecedentes',
        'intervenciones_externas',
        'actividades_extraescolares',
        'situacion_escolar',
        'situacion_salud',
        'situacion_familiar',
        'situacion_socioeconomica',
        'fk_persona',
        'fk_aula',
    ];

    protected $casts = [
        'inasistencias' => 'integer',
        'cud' => 'boolean',
    ];


    public function getDescripcionAttribute(){
        return $this->persona ? "{$this->persona->nombre} {$this->persona->apellido}" : 'Sin datos';
    }

     public function persona(): BelongsTo {
        return $this->belongsTo(Persona::class, 'fk_persona', 'id_persona');
    }

    public function aula(): BelongsTo {
        return $this->belongsTo(Aula::class, 'fk_aula', 'id_aula');
    }

    public function familiares(): BelongsToMany{        
        return $this->belongsToMany(Familiar::class, 'tiene_familiar', 'fk_alumno', 'fk_familiar');
    }

    public function hermanos(): BelongsToMany{
        return $this->belongsToMany(Alumno::class, 'es_hermano_de', 'fk_alumno', 'fk_alumno_hermano');
    }

    public function intervenciones(): BelongsToMany{
        return $this->belongsToMany(Intervencion::class, 'intervencion_alumno', 'fk_alumno', 'fk_intervencion');
    }

    public function documentos(){
        return $this->hasMany(Documento::class, 'fk_alumno', 'id_alumno');
    }

    public function planesDeAccion(): BelongsToMany{
        return $this->belongsToMany(PlanDeAccion::class, 'tiene_asignado', 'fk_alumno', 'fk_plan');
    }
}