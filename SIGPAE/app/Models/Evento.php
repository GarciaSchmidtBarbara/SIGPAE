<?php
    
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Profesional; 
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Asiste;


class Evento extends Model{
        protected $table = 'eventos';

        protected $primaryKey = 'id_evento';

        protected $fillable = [
            'fecha_hora',
            'lugar',
            //'otros_asistentes', no va en filleable porque se maneja como arreglo
            'Fk_profesional_creador',
            'tipo',
            'notas', //No pongo alumnos y profesionales porque se manejan con relaciones
            'es_derivacion_externa',
            'profesional_tratante',
            'periodo_recordatorio',
        ];

        public function creador(): BelongsTo
        {
            return $this->belongsTo(Profesional::class, 'Fk_profesional_creador');
        }

        public function alumnos(): BelongsToMany
        {
            //tabla relacion será evento_alumno
            return $this->belongsToMany(Alumno::class, 'evento_alumno', 'id_evento', 'id_alumno');
        }

        public function profesionalesAsistentes(): BelongsToMany
        {
            // tabla relacion será evento_profesional
            return $this->belongsToMany(Profesional::class, 'evento_profesional', 'id_evento', 'id_profesional')
                        ->using(Asiste::class)
                        ->withPivot('asistio', 'asistencia_confirmada')
                        ->withTimestamps();
        }

        public function agregarProfesionales(array $profesionalIds): void
        {
            //agrega profesionales a la lista de asistentes
            // Si el array tiene índices numéricos -> lista simple de ids
            // Si el array tiene la forma [id => ['asistio' => true, ...]] se respetarán los valores del pivot
            $this->profesionalesAsistentes()->syncWithoutDetaching($profesionalIds);
        }

        public function agregarAlumnos(array $alumnoIds): void
        {
            //agrega alumnos
            $this->alumnos()->syncWithoutDetaching($alumnoIds);
        }

        public static function obtenerEventosDeProfesional(int $profesionalId)
        {
            return self::where('creador_id', $profesionalId)->get();
        }
        //Para notificar invesigue que se usa observadores de laravel o eventos del dominio. No van en el modelo

        protected $casts = [
            'fecha_hora' => 'datetime',
            'es_derivacion_externa' => 'boolean',
            'periodo_recordatorio' => 'integer',
        ];

        public function aulas(): BelongsToMany
        {
            //Lo mismo que arriba, tabla relación evento_aula
            return $this->belongsToMany(Aula::class, 'evento_aula', 'id_evento', 'id_aula');
        }

        public function agregarAulas(array $aulaIds): void
        {  
            $this->aulas()->syncWithoutDetaching($aulaIds);
        }
}