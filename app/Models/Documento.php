<?php

namespace App\Models;

use App\Enums\TipoFormato;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';
    protected $primaryKey = 'id_documento';

    const CREATED_AT = 'fecha_hora_carga';
    const UPDATED_AT = null;

    /** Tipos de archivo permitidos (mapa extensión → mime) */
    const MIMES_PERMITIDOS = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    /** 10 MB máximo */
    const MAX_SIZE_BYTES = 10 * 1024 * 1024;

    protected $fillable = [
        'nombre',
        'contexto',
        'tipo_formato',
        'disponible_presencial',
        'ruta_archivo',
        'tamanio_archivo',
        'fk_id_alumno',
        'fk_id_profesional',
        'fk_id_plan_de_accion',
        'fk_id_intervencion',
        'fk_id_evaluacion_plan_de_accion',
        'fk_id_evaluacion_intervencion_espontanea',
    ];

    protected $casts = [
        'fecha_hora_carga'      => 'datetime',
        'tamanio_archivo'       => 'integer',
        'disponible_presencial' => 'boolean',
        'tipo_formato'          => TipoFormato::class,
    ];

    // ── Relationships ─────────────────────────────────────────

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'fk_id_alumno', 'id_alumno');
    }

    public function profesionalCarga(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional', 'id_profesional');
    }

    public function planDeAccion(): BelongsTo
    {
        return $this->belongsTo(PlanDeAccion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(Intervencion::class, 'fk_id_intervencion', 'id_intervencion');
    }

    public function evaluacionIntervencionEspontanea(): BelongsTo
    {
        return $this->belongsTo(EvaluacionDeIntervencionEspontanea::class, 'fk_id_evaluacion_intervencion_espontanea', 'id_evaluacion_intervencion_espontanea');
    }

    public function evaluacionPlanDeAccion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionDePlan::class, 'fk_id_evaluacion_plan_de_accion', 'id_evaluacion_plan_de_accion');
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getEtiquetaContextoAttribute(): string
    {
        return match ($this->contexto) {
            'perfil_alumno' => 'Perfil de alumno',
            'plan_accion'   => 'Plan de acción',
            'intervencion'  => 'Intervención',
            'institucional' => 'Institucional',
            default         => ucfirst($this->contexto ?? ''),
        };
    }

    public function getPerteneceAAttribute(): string
    {
        return match ($this->contexto) {
            'perfil_alumno' => $this->alumno?->persona
                ? trim($this->alumno->persona->apellido . ', ' . $this->alumno->persona->nombre)
                : '—',
            'plan_accion'  => $this->planDeAccion?->descripcion ?? '—',
            'intervencion' => $this->intervencion
                ? 'Intervención N°' . $this->fk_id_intervencion
                : '—',
            default => '—',
        };
    }

    public function getTamanioFormateadoAttribute(): string
    {
        $bytes = $this->tamanio_archivo ?? 0;
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2) . ' MB';
        }
        return number_format($bytes / 1_024, 1) . ' KB';
    }

    /** ¿El archivo puede visualizarse en el navegador? */
    public function getVisualizableOnlineAttribute(): bool
    {
        return in_array(strtolower($this->tipo_formato->value ?? ''), ['pdf', 'jpg', 'png']);
    }
}
