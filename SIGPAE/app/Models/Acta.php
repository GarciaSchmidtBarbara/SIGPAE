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

    protected $primaryKey = 'id_acta';

    protected $fillable = [
        'tipo_acta',
        'fecha_hora',
        'temario',
        'acuerdos',
        'observaciones',
        'fecha_hora_proxima_reunion',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'temario' => 'string', // será tratado como text
        'acuerdos' => 'string', // será tratado como text
        'observaciones' => 'string', // será tratado como text
        'fecha_hora_proxima_reunion' => 'datetime',
        'tipo_acta' => TipoActa::class,
    ];

    // revisado
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'fk_id_aula', 'id_aula');
    }

    // revisado
    public function tieneProfesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'acta_profesional', 'fk_id_acta', 'fk_id_profesional');
    }

    // revisado
    public function otrosAsistentesA(): HasMany {
        return $this->hasMany(OtroAsistenteA::class, 'fk_id_acta', 'id_acta');
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