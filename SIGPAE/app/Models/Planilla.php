<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- 1. IMPORTANTE: Importamos la Papelera

class Planilla extends Model
{
    use SoftDeletes; // <--- 2. IMPORTANTE: Activamos la Papelera en este modelo

    protected $primaryKey = 'id_planilla';

    protected $fillable = [
        'nombre_planilla',
        'tipo_planilla',
        'anio',
        'datos_planilla',
    ];

    protected $casts = [
        'anio' => 'integer',
        'datos_planilla' => 'array', // Esto es vital para que tus actas funcionen
        'deleted_at' => 'datetime',  // <--- 3. Recomendado: Para manejar bien la fecha de borrado
    ];

    // Relaciones (se mantienen igual)
    public function intervenciones()
    {
        return $this->belongsToMany(Intervencion::class, 'intervencion_planilla', 'fk_id_planilla', 'fk_id_intervencion');
    }
}