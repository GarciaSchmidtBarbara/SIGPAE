<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtroAsistenteA extends Model
{
    protected $table = 'otros_asistentes_a';

    protected $primaryKey = 'id_otro_asistente_a';

    protected $fillable = [
        'nombre',
        'apellido',
        'funcion'
    ];

    // revisado
    public function acta(): belongsTo
    {
        return $this->belongsTo(Acta::class, 'fk_id_acta', 'id_acta');
    }
}
