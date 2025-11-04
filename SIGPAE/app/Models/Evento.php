<?php
    
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Profesional;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Asiste;
use App\Enums\TipoEvento;

class Evento extends Model{

        protected $primaryKey = 'id_evento';

        protected $fillable = [
            'fecha_hora',
            'lugar',
            //'otros_asistentes', no va en filleable porque se maneja como arreglo
            'tipo_evento',
            'notas',
            'es_derivacion_externa', // boolean
            'profesional_tratante', // preguntar a Lucas
            'periodo_recordatorio', // integer en dias
        ];

        protected $casts = [
            'fecha_hora' => 'datetime',
            'es_derivacion_externa' => 'boolean',
            'periodo_recordatorio' => 'integer',
            'tipo_evento' => TipoEvento::class,
        ];

        
        // revisado
        public function profesionalCreador(): BelongsTo
        {
            return $this->belongsTo(Profesional::class, 'fk_id_profesional_creador', 'id_profesional');
        }

        // revisado
        public function esInvitadoA(): BelongsToMany
        {
            return $this->hasMany(EsInvitadoA::class, 'fk_id_evento', 'id_evento');
        }

        // revisado
        public function alumnos(): BelongsToMany
        {
            return $this->belongsToMany(Alumno::class, 'evento_alumno', 'fk_id_evento', 'fk_id_alumno');
        }

        public function agregarProfesionales(array $profesionalIds): void
        {
            //agrega profesionales a la lista de asistentes
            // Si el array tiene índices numéricos -> lista simple de ids
            // Si el array tiene la forma [id => ['asistio' => true, ...]] se respetarán los valores del pivot
            // $this->profesionalesAsistentes()->syncWithoutDetaching($profesionalIds);
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

        // revis
        public function aulas(): BelongsToMany
        {
            return $this->belongsToMany(Aula::class, 'tiene_aulas', 'fk_id_evento', 'fk_id_aula');
        }

        public function agregarAulas(array $aulaIds): void
        {  
            $this->aulas()->syncWithoutDetaching($aulaIds);
        }
}