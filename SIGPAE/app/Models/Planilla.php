<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TipoPlanilla;

class Planilla extends Model
{
    protected $primaryKey = 'id_planilla';

    protected $fillable = [
        'nombre_planilla',
        'tipo_planilla',
        'anio',
    ];

    protected $casts = [
        'tipo_planilla' => TipoPlanilla::class,
        'anio' => 'integer',
    ];
    // revisado
    public function intervenciones(): BelongsToMany
    {
        return $this->belongsToMany(Intervencion::class, 'intervencion_planilla', 'fk_id_planilla', 'fk_id_intervencion');
    }
}
