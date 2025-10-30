<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;

class Familiar extends Model
{
    protected $table = 'familiares';

    protected $primaryKey = 'id_familiar';

    protected $fillable = [
        'lugar_de_trabajo',
        'telefono_de_trabajo',
        'fk_id_persona',
        'lugar_de_trabajo',
        'parentesco',
    ];

    public function getDescripcionAttribute()
    {
        return $this->persona->nombre . ' ' . $this->persona->apellido . ' (' . $this->parentesco . ')';
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'fk_id_persona');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'tiene_familiar', 'id_familiar', 'id_alumno');
    }
}