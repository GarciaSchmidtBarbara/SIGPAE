<?php

namespace App\Models;

use App\Enums\Parentesco;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Familiar extends Model
{
    protected $table = 'familiares';

    protected $primaryKey = 'id_familiar';

    protected $fillable = [
        'fk_id_persona',
        'lugar_de_trabajo',
        'telefono_personal',
        'telefono_laboral',
    ];

    public function getDescripcionAttribute()
    {
        return $this->persona->nombre . ' ' . $this->persona->apellido . ' (' . $this->parentesco . ')';
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'fk_id_persona', 'id_persona');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'tiene_familiar', 'fk_id_familiar', 'fk_id_alumno')
                    ->using(TieneFamiliar::class)
                    ->withPivot('id_tiene_familiar', 'parentesco', 'otro_parentesco', 'activa', 'observaciones')
                    ->withTimestamps();
    }
}