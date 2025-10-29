<?php

namespace App\Modules\Personas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\HasOne;

class Persona extends Model
{
    protected $table = 'personas';

    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'fecha_nacimiento',
        'domicilio',
        'nacionalidad',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function getDescripcionAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    // Relaciones a dejar como referencia; se pueden activar si se mueven los modelos relacionados
    // public function alumno(): HasOne
    // {
    //     return $this->hasOne(Alumno::class, 'fk_id_persona');
    // }
}
