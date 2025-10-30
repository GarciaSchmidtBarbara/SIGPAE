<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    // clave primaria personalizada
    protected $primaryKey = 'id_documento';

    const CREATED_AT = 'fecha_hora_carga';

    protected $fillable = [
        'nombre',
        'disponible_presencial',
        'ruta_archivo',
        'tipo_documento',
        'fk_id_usuario_carga',
        'tamano_archivo',
    ];
}
