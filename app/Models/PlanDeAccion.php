<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\TipoPlan;
use App\Enums\EstadoPlan;

class PlanDeAccion extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'planes_de_accion';

    protected $primaryKey = 'id_plan_de_accion';

    protected $fillable = [
        'estado_plan',
        'tipo_plan', 
        'objetivos',
        'activo',
        'acciones',
        'observaciones',
        'fk_id_profesional_generador',
    ];

    public $timestamps = true;

    public function getDescripcionAttribute()
    {
        return "PLAN {$this->tipo_plan->value} N°{$this->id_plan_de_accion}";
    }

    protected $casts = [
        'activo' => 'boolean',
        'objetivos' => 'string', // será tratado como text
        'observaciones' => 'string', // será tratado como text
        'acciones' => 'string', // será tratado como text
        'tipo_plan' => TipoPlan::class,
        'estado_plan' => EstadoPlan::class,
    ];

    public function profesionalGenerador(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_generador', 'id_profesional');
    }

    public function profesionalesParticipantes(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'participa_plan', 'fk_id_plan_de_accion', 'fk_id_profesional');
    }

    public function intervenciones(): hasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    public function evaluaciones(): hasMany
    {
        return $this->hasMany(EvaluacionDePlan::class, 'fk_id_plan_de_accion', 'id_plan_de_accion');
    }

    public function aulas():BelongsToMany
    {
        return $this->belongsToMany(Aula::class, 'incluye', 'fk_id_plan_de_accion', 'fk_id_aula');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'tiene_asignado', 'fk_id_plan_de_accion', 'fk_id_alumno')
        ->with('persona', 'aula');
    }

    public function documentos()
    {
        return collection(); // Placeholder para evitar errores
    }
    // TODO: implementar cuando exista la lógica de documentos
    /**
     * public function documentos(): HasMany{
     * return $this->hasMany(Documento::class, 'fk_id_plan_accion', 'id_plan_de_accion');
     * }
       */
}
