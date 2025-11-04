<?php

namespace App\Models;

use App\Enums\Parentesco;
use Illuminate\Database\Eloquent\Model;
use App\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;

class Familiar extends Model
{
    protected $table = 'familiares';

    protected $primaryKey = 'id_familiar';

    protected $fillable = [
        'lugar_de_trabajo',
        'observaciones',
        'telefono_personal',
        'telefono_laboral',
        'lugar_de_trabajo',
        'otro_parentesco',
        'parentesco',
    ];

    protected $casts = [
        'parentesco' => Parentesco::class,
    ];
    
    public function getDescripcionAttribute()
    {
        return $this->persona->nombre . ' ' . $this->persona->apellido . ' (' . $this->parentesco . ')';
    }

    // revisado
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'fk_id_persona', 'id_persona');
    }

    // revisado
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'tiene_familiar', 'fk_id_familiar', 'fk_id_alumno');
    }
}