<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TipoPlan;
use App\Enums\EstadoPlan;

class PlanDeAccion extends Model
{
    protected $table = 'planes_de_accion';

    protected $primaryKey = 'id_plan_de_accion';

    const CREATED_AT = 'fecha_hora';

    protected $fillable = [
        'estado_plan',
        'tipo_plan', 
        'objetivos',
        'activo',
        'fecha_hora',
        'acciones',
        'observaciones',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'activo' => 'boolean',
        'objetivos' => 'string', // será tratado como text
        'observaciones' => 'string', // será tratado como text
        'acciones' => 'string', // será tratado como text
        'tipo_plan' => TipoPlan::class,
        'estado_plan' => EstadoPlan::class,
    ];

    // revisado
    public function profesionalGenerador(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_generador', 'id_profesional');
    }

    // revisado
    public function profesionalesParticipantes(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'participa_plan', 'fk_id_plan_de_accion', 'fk_id_profesional');
    }

    // revisado
    public function intervenciones(): hasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    // revisado
    public function evaluaciones(): hasMany
    {
        return $this->hasMany(EvaluacionDePlan::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    // revisado
    public function aulas():BelongsToMany
    {
        return $this->belongsToMany(Aula::class, 'incluye', 'fk_id_plan_de_accion', 'fk_id_aula');
    }

    // revisado
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'tiene_asignado', 'fk_id_plan_de_accion', 'fk_id_alumno');
    }
    // revisado
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'fk_id_plan_accion', 'id_plan_de_accion');
    }
}
