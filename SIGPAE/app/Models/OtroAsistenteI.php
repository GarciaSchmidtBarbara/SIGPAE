<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtroAsistenteI extends Model
{
    protected $table = 'otros_asistentes_i';

    protected $primaryKey = 'id_otro_asistente_i';

    protected $fillable = [
        'nombre',
        'apellido',
        'descripcion'
    ];

    public function intervencion(): belongsTo
    {
        return $this->belongsTo(Intervencion::class, 'fk_id_intervencion', 'id_intervencion');
    }
}
