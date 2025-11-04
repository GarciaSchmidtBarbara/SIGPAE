<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Aula extends Model
{
    use HasFactory;
    protected $table = 'aulas';

    protected $primaryKey = 'id_aula';

    protected $fillable = [
        'curso',
        'division',
    ];

    public function getDescripcionAttribute()
    {
        return "{$this->curso}°{$this->division}";
    }

    // revisado
    public function intervenciones(): BelongsToMany{
        return $this->belongsToMany(Intervencion::class, 'intervencion_aula', 'fk_id_aula', 'fk_id_intervencion');
    }

    // revisado
    public function planesDeAccion():BelongsToMany
    {
        return $this->belongsToMany(PlanDeAccion::class, 'incluye', 'fk_id_aula', 'fk_id_plan_de_accion');
    }

    // revisado
    public function alumnos(): HasMany {
        return $this->hasMany(Alumno::class, 'fk_id_aula', 'id_aula');
    }

    // revisado
    public function actas(): HasMany {
        return $this->hasMany(Acta::class, 'fk_id_acta', 'id_acta');
    }

    // revisado
    public function eventos():BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'tiene_aulas', 'fk_id_aula', 'fk_id_evento');
    }
}