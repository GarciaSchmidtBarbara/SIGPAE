<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;
use App\Enums\Modalidad;

class Intervencion extends Model
{
    protected $table = 'intervenciones';

    protected $primaryKey = 'id_intervencion';

    protected $fillable = [
        'fecha_hora_intervencion',
        'lugar',
        'modalidad',
        'otra_modalidad',
        'temas_tratados',
        'compromisos',
        'observaciones',
        'activo',
        'tipo_intervencion',
        'fk_id_plan_de_accion',
        'fk_id_profesional_genera',
        'fk_id_evaluacion_intervencion_espontanea',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'temas_tratados' => 'string', // será tratado como text
        'compromisos' => 'string', // será tratado como text
        'observaciones' => 'string', // será tratado como text
        'modalidad' => Modalidad::class,
    ];

    public function getDescripcionAttribute(){
        return $this->lugar . ' ' . $this->fecha . ' ' . $this->tipo;
    }

    // revisado
    public function planDeAccion(): BelongsTo {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    // revisado
    public function profesionalGenerador(): BelongsTo {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_genera', 'id_profesional');
    }

    // revisado
    public function profesionales(): BelongsToMany {
        return $this->belongsToMany(Profesional::class, 'reune', 'fk_id_intervencion', 'fk_id_profesional');
    }

    // revisado
    public function aulas(): BelongsToMany{
        return $this->belongsToMany(Aula::class, 'intervencion_aula', 'fk_id_intervencion', 'fk_id_aula');
    }
    
    // revisado
    public function alumnos(): BelongsToMany{
        return $this->belongsToMany(Alumno::class, 'intervencion_alumno', 'fk_id_intervencion', 'fk_id_alumno');
    }

    // revisado
    public function evaluacionIntervencionEspontanea(): BelongsTo{
        return $this->belongsTo(EvaluacionDeIntervecionEspontanea::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }

    // revisado
    public function documentos(): hasMany{
        return $this->hasMany(Documento::class, 'fk_id_intervencion', 'id_intervencion');
    }

    // revisado
    public function otros_asistentes_i(): hasMany {
        return $this->hasMany(OtroAsistenteI::class, 'fk_id_intervencion', 'id_intervencion');
    }

    // revisado
    public function planillas(): BelongsToMany
    {
        return $this->belongsToMany(Planilla::class, 'intervencion_planilla', 'fk_id_intervencion', 'fk_id_planilla');
    }
}