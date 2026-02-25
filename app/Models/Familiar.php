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
        // Accedemos a nombre y apellido directamente a través de la relación
        $texto = $this->persona->nombre . ' ' . $this->persona->apellido;
        
        if ($this->pivot && $this->pivot->parentesco) {
            
            // 1. Extraemos el string del objeto Enum usando ->value
            $valorParentesco = $this->pivot->parentesco->value;
            
            // 2. Verificamos si es "OTRO" para mostrar el texto libre
            if ($valorParentesco === 'OTRO' && !empty($this->pivot->otro_parentesco)) {
                $texto .= ' (' . $this->pivot->otro_parentesco . ')';
            } else {
                $texto .= ' (' . $valorParentesco . ')';
            }
        }

        return $texto;
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