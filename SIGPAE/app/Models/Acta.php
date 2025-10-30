<?php

namespace App\Models;

use App\Enums\TipoActa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Acta extends Model
{
    use HasFactory;

    protected $table = 'actas';

    protected $fillable = [
        'tipo_acta',
        'fecha_hora',
        'otros_participantes', // Lista de strings como JSON
        'temario',
        'acuerdos',
        'observaciones',
        'fecha_proxima_reunion',
        'fk_aula',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_proxima_reunion' => 'datetime',
        'otros_participantes' => 'array', 
        'tipo_acta' => TipoActa::class,
    ];

    // Relaciones
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'fk_aula');
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(
            Profesional::class,
            'acta_profesional',
            'fk_acta',   
            'fk_profesional'
        );
    }

    public function otrosAsistentes(): HasMany {
        return $this->hasMany(OtroAsistenteA::class, 'fk_acta');
    }
    

    // Métodos personalizados
    public static function crearActa(array $data): Acta
    {
        return self::create($data);
    }

    public function borrarActa(): void
    {
        $this->delete();
    }

    public function agregarProfesional(Profesional $profesional): void
    {
        $this->profesionales()->attach($profesional->id);
    }

    public function agregarOtroAsistente(string $otroAsistente): void
    {
        $participantes = $this->otros_participantes ?? [];
        $participantes[] = $otroAsistente;
        $this->update(['otros_participantes' => $participantes]);
    }

    public function agregarAula(Aula $aula): void
    {
        $this->update(['fk_id_aula' => $aula->id]);
    }
}