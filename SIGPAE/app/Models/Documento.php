<?php
// revisado atributos
namespace App\Models;

use App\Enums\TipoFormato;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    // clave primaria personalizada
    protected $primaryKey = 'id_documento';

    const CREATED_AT = 'fecha_hora_carga';

    protected $fillable = [
        'nombre',
        'tipo_formato',
        'disponible_presencial',
        'ruta_archivo', // revisar
        'tamanio_archivo',
    ];

    protected $casts = [
        'fecha_hora_carga' => 'datetime',
        'tamanio_archivo' => 'integer',
        'disponible_presencial' => 'boolean',
        'tipo_formato' => TipoFormato::class,
    ];

    // revisado
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'fk_id_alumno', 'id_alumno');
    }

    // revisado
    public function profesionalCarga(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional', 'id_profesional');
    }

    // revisado
    public function planDeAccion(): BelongsTo
    {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    // revisado
    public function evaluacionIntervencionEspontanea(): BelongsTo
    {
        return $this->belongsTo(EvaluacionDeIntervencionEspontanea::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }

    // revisado
    public function evaluacionPlanDeAccion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionDePlan::class, 'fk_id_evaluacion_plan_de_accion', 'id_evaluacion_plan_de_accion');
    }
}
