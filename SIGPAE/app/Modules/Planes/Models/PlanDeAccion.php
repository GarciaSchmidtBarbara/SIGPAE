<?php
namespace App\Modules\Planes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Profesionales\Models\Profesional;
use App\Modules\Personas\Models\Alumno;
use App\Models\Aula;

class PlanDeAccion extends Model
{
    protected $table = 'planes_de_accion';

    protected $primaryKey = 'id_plan_de_accion';

    protected $fillable = [
        'estado',
        'tipo',
        'fecha_creacion',
        'fk_id_profesional_creador',
    ];

    public function profesionalCreador(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional_creador');
    }

    public function responsables(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'responsables', 'fk_id_plan', 'fk_id_profesional_responsable');
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_plan');
    }

    public function evaluaciones(): HasOne
    {
        return $this->hasOne(Evaluacion::class, 'fk_id_plan');
    }

    public function documentaciones(): HasMany
    {
        return $this->hasMany(Documentacion::class, 'fk_id_plan_accion');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'fk_id_aula', 'id_aula');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'plan_alumno', 'fk_id_plan', 'fk_id_alumno');
    }

}
