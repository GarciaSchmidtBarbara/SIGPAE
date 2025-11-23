<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;
use Illuminate\Database\Eloquent\relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\Modalidad;


class Intervencion extends Model
{
    use HasFactory;
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
        'temas_tratados' => 'string', 
        'compromisos' => 'string', 
        'observaciones' => 'string', 
        'fecha_hora_intervencion' => 'datetime',
        'modalidad' => Modalidad::class,
    ];

    public function getDescripcionAttribute(){
        return $this->lugar . ' ' . $this->fecha . ' ' . $this->tipo;
    }

    public function planDeAccion(): BelongsTo {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    public function profesionalGenerador(): BelongsTo {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_genera', 'id_profesional');
    }
    public function profesionales(): BelongsToMany {
        return $this->belongsToMany(Profesional::class, 'reune', 'fk_id_intervencion', 'fk_id_profesional');
    }

    public function aulas(): BelongsToMany{
        return $this->belongsToMany(Aula::class, 'intervencion_aula', 'fk_id_intervencion', 'fk_id_aula');
    }
    
    public function alumnos(): BelongsToMany{
        return $this->belongsToMany(Alumno::class, 'intervencion_alumno', 'fk_id_intervencion', 'fk_id_alumno');
    }

    public function evaluacionDeIntervencionEspontanea(): BelongsTo{
        return $this->belongsTo(EvaluacionDeIntervencionEspontanea::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }

    public function documentos(): HasMany{
        return $this->hasMany(Documento::class, 'fk_id_intervencion', 'id_intervencion');
    }

    public function otros_asistentes_i(): HasMany {
        return $this->hasMany(OtroAsistenteI::class, 'fk_id_intervencion', 'id_intervencion');
    }

    public function planillas(): BelongsToMany
    {
        return $this->belongsToMany(Planilla::class, 'intervencion_planilla', 'fk_id_intervencion', 'fk_id_planilla');
    }
}