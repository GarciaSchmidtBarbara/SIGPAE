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

    public function eventos(): BelongsToMany {
        return $this->belongsToMany(Evento::class, 'evento_alumno', 'fk_alumno', 'fk_evento');
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
}