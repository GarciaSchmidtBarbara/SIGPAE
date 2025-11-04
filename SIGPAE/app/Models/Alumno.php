<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;


class Alumno extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'cud',
        'inasistencias',
        'observaciones',
        'antecedentes',
        'intervenciones_externas',
        'actividades_extraescolares',
        'situacion_escolar',
        'situacion_medica',
        'situacion_familiar',
        'situacion_socioeconomica',
    ];

    protected $casts = [
        'inasistencias' => 'integer',
        'cud' => 'boolean',
        'observaciones' => 'string',
        'antecedentes' => 'string',
        'intervenciones_externas' => 'string',
        'actividades_extraescolares' => 'string',
        'situacion_escolar' => 'string',
        'situacion_medica' => 'string',
        'situacion_familiar' => 'string',
        'situacion_socioeconomica' => 'string',
    ];


    public function getDescripcionAttribute(){
        return $this->persona ? "{$this->persona->nombre} {$this->persona->apellido}" : 'Sin datos';
    }

    // revisado
     public function persona(): BelongsTo {
        return $this->belongsTo(Persona::class, 'fk_id_persona', 'id_persona');
    }

    // revisado
    public function aula(): BelongsTo {
        return $this->belongsTo(Aula::class, 'fk_id_aula', 'id_aula');
    }

    // revisado
    public function familiares(): BelongsToMany{
        return $this->belongsToMany(Familiar::class, 'tiene_familiar', 'fk_id_alumno', 'fk_id_familiar');
    }

    // revisado
    public function hermanos(): BelongsToMany{
        return $this->belongsToMany(Alumno::class, 'es_hermano_de', 'fk_id_alumno', 'fk_id_alumno_hermano');
    }

    // revisado
    public function esHermanoDe(): BelongsToMany{
        return $this->belongsToMany(Alumno::class, 'es_hermano_de', 'fk_id_alumno_hermano',  'fk_id_alumno');
    }

    // revisado
    public function intervenciones(): BelongsToMany{
        return $this->belongsToMany(Intervencion::class, 'intervencion_alumno', 'fk_id_alumno', 'fk_id_intervencion');
    }

    // revisado
    public function documentos(){
        return $this->hasMany(Documento::class, 'fk_id_alumno', 'id_alumno');
    }

    // revisado
    public function planesDeAccion(): BelongsToMany{
        return $this->belongsToMany(PlanDeAccion::class, 'tiene_asignado', 'fk_id_alumno', 'fk_id_plan_de_accion');
    }

    // revisado
    public function eventos(): BelongsToMany {
        return $this->belongsToMany(Evento::class, 'evento_alumno', 'fk_id_alumno', 'fk_id_evento');
    }

    // Métodos personalizados
    public static function crearAlumno(array $data): Alumno
    {
        return self::create($data);
    }

    public function borrarAlumno(): void
    {
        $this->delete();
    }

    public function agregarFamiliar(Familiar $familiar): void
    {
        $this->familiares()->attach($familiar->id);
    }

    public function agregarHermano(Alumno $hermano): void
    {
        $this->hermanos()->attach($hermano->id);
    }

    public function agregarPlanDeAccion(PlanDeAccion $plan): void
    {
        $this->planesDeAccion()->attach($plan->id);
    }

    public function agregarIntervencion(Intervencion $intervencion): void
    {
        $this->intervenciones()->attach($intervencion->id);
    }

    public function agregarAula(Aula $aula): void
    {
        $this->update(['fk_aula' => $aula->id]);
    }

    public function agregarDocumento(Documento $documento): void
    {
        $this->documentos()->save($documento);
    }

    public function borrarDocumento(Documento $documento): void
    {
        if ($this->documentos->contains($documento)) {
            $documento->delete();
        }
    }

    public function getActivoTextoAttribute(): string {
        return $this->persona?->activo ? 'Sí' : 'No';
    }

    public function getCudTextoAttribute(): string {
        return $this->getAttribute('cud') ? 'Sí' : 'No';
    }

}