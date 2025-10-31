<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Asiste extends Pivot
{
    // No usamos HasFactory aquí; la tabla ya existe y no tiene id autoincremental

    protected $table = 'evento_profesional';

    // La tabla existente no tiene una columna id autoincremental. Usamos llave compuesta
    public $incrementing = false;
    protected $primaryKey = null;

    // permitir asignación masiva para los campos pivot
    protected $fillable = [
        'id_evento',
        'id_profesional',
        'asistio',
        'asistencia_confirmada',
    ];

    protected $casts = [
        'asistio' => 'boolean',
        'asistencia_confirmada' => 'boolean',
    ];

    public $timestamps = true;
}
