<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Enums\TipoPlanilla; // <--- COMENTAMOS ESTO para evitar el error del nombre largo

class Planilla extends Model
{
    protected $primaryKey = 'id_planilla';

    protected $fillable = [
        'nombre_planilla',
        'tipo_planilla',
        'anio',
        'datos_planilla', // <--- AGREGADO: ¡Fundamental para guardar el acta!
    ];

    protected $casts = [
        // 'tipo_planilla' => TipoPlanilla::class, // <--- COMENTADO: Así acepta cualquier texto
        'anio' => 'integer',
        'datos_planilla' => 'array', // <--- AGREGADO: Para que Laravel maneje el JSON solo
    ];

    // Relaciones (déjalas como están)
    public function intervenciones()
    {
        return $this->belongsToMany(Intervencion::class, 'intervencion_planilla', 'fk_id_planilla', 'fk_id_intervencion');
    }
}


