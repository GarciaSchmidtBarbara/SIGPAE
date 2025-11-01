<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Alumno;
use App\Models\Profesional;
use App\Models\Familiar;
use App\Models\Hermano;

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
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function getDescripcionAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

     public function alumno(): HasOne
    {   //Como es 0,1 relacion, usamos hasOne y el alumno tendrá la clave foránea
        return $this->hasOne(Alumno::class, 'fk_id_persona');
    }

    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class, 'fk_id_persona');
    }

    public function familiar(): HasOne
    {
        return $this->hasOne(Familiar::class, 'fk_id_persona');
    }

    public function hermano(): HasOne
    {
        return $this->hasOne(Hermano::class, 'fk_id_persona');
    }
}