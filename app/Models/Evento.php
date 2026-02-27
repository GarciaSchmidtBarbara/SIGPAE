<?php
    
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Profesional;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Asiste;
use App\Enums\TipoEvento;

class Evento extends Model{
        use HasFactory;

        protected $primaryKey = 'id_evento';

        protected $fillable = [
            'fecha_hora',
            'lugar',
            //'otros_asistentes', no va en filleable porque se maneja como arreglo
            'tipo_evento',
            'notas',
            'profesional_tratante', // preguntar a Lucas
            'periodo_recordatorio', // integer en dias
            'ultimo_recordatorio_at',
            'fk_id_profesional_creador',
        ];

        protected $casts = [
            'fecha_hora' => 'datetime',
            'periodo_recordatorio' => 'integer',
            'tipo_evento' => TipoEvento::class,
            'ultimo_recordatorio_at' => 'datetime',
        ];

        
        // revisado
        public function profesionalCreador(): BelongsTo
        {
            return $this->belongsTo(Profesional::class, 'fk_id_profesional_creador', 'id_profesional');
        }

        // revisado
        public function esInvitadoA(): HasMany
        {
            return $this->hasMany(EsInvitadoA::class, 'fk_id_evento', 'id_evento');
        }

        // revisado
        public function alumnos(): BelongsToMany
        {
            return $this->belongsToMany(Alumno::class, 'evento_alumno', 'fk_id_evento', 'fk_id_alumno');
        }

        // revisado
        public function aulas(): BelongsToMany
        {
            return $this->belongsToMany(Aula::class, 'tiene_aulas', 'fk_id_evento', 'fk_id_aula');
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

        public function agregarAulas(array $aulaIds): void
        {  
            $this->aulas()->syncWithoutDetaching($aulaIds);
        }
}